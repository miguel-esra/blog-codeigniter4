<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\CIAuth;
use App\Models\User;
use Config\Services;
USE App\Libraries\Hash;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Models\Category;
use SSP;
use SawaStacks\CodeIgniter\Slugify;
use App\Models\Subcategory;

class AdminController extends BaseController
{
    protected $helpers = ['url', 'form', 'CIMail', 'CIFunctions'];
    protected $db;

    public function __construct()
    {
        require_once APPPATH.'ThirdParty/ssp.php';
        $this->db = db_connect();
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Dashboard',
        ];
        return view('backend/pages/home', $data);
    }

    public function logoutHandler()
    {
        CIAuth::forget();
        return redirect()->route('admin.login.form')->with('fail', 'You are logged out!');
    }

    public function profile()
    {
        $data = array(
            'pageTitle' => 'Profile'
        );
        return view('backend/pages/profile', $data);
    }

    public function updatePersonalDetails()
    {
        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $user_id = CIAuth::id();

        if ( $request->isAJAX() ) {
            $this->validate([
                'name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Full name is required.'
                    ]
                ],
                'username' => [
                    'rules' => 'required|min_length[4]|is_unique[users.username,id,' . $user_id . ']',
                    'errors' => [
                        'required' => 'Username is required.',
                        'min_length' => 'Username must have a minimum of 4 characters.',
                        'is_unique' => 'Username is already taken!'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return json_encode(['status' => 0, 'error' => $errors]);
            } else {
                $user = new User();
                $update = $user->where('id', $user_id)->set([
                                    'name' => $request->getVar('name'),
                                    'username' => $request->getVar('username'),
                                    'bio' => $request->getVar('bio'),
                                ])->update();

                if ( $update) {
                    $user_info = $user->find($user_id);
                    return json_encode(['status' => 1, 'user_info' => $user_info, 'msg' => 'Your personal details have been successfully updated.']);
                } else {
                    return json_encode(['status' => 0, 'msg' => 'Something went wrong.']);
                }
            }
        }
    }

    public function updateProfilePicture()
    {
        $request = \Config\Services::request();
        $user_id = CIAuth::id();

        $user = new User();
        $user_info = $user->asObject()->where('id', $user_id)->first();

        $path = 'images/users/';
        $file = $request->getFile('user_profile_file');

        $old_picture = $user_info->picture;
        $new_filename = 'UIMG_' . $user_id . $file->getRandomName();

        // if ( $file->move($path, $new_filename) ) {
        //     if ( $old_picture != null && file_exists($path . $old_picture)) {
        //         unlink($path . $old_picture);
        //     }
        //     $user->where('id', $user_info->id)->set(['picture' => $new_filename])->update();
        //     echo json_encode(['status' => 1, 'msg' => 'Done! Your profile picture has been successfully updated.']);
        // } else {
        //     echo json_encode(['status' => 0, 'msg' => 'Something went wrong.']);
        // }

        // Image manipulation
        $upload_image = \Config\Services::image()->withFile($file)->resize(450,450,true,'height')->save($path . $new_filename);

        if ( $upload_image ) {
            if ( $old_picture != null && file_exists($path . $old_picture)) {
                unlink($path . $old_picture);
            }
            $user->where('id', $user_info->id)->set(['picture' => $new_filename])->update();
            echo json_encode(['status' => 1, 'msg' => 'Done! Your profile picture has been successfully updated.']);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Something went wrong.']);
        }

    }

    public function changePassword()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $validation = \Config\Services::validation();
            $user_id = CIAuth::id();
            $user = new User();
            $user_info = $user->asObject()->where('id', $user_id)->first();

            // Validate the form
            $this->validate([
                'current_password' => [
                    'rules' => 'required|min_length[5]|check_current_password[current_password]',
                    'errors' => [
                        'required' => 'Enter current password.',
                        'min_length' => 'The password must have at least 5 characters.',
                        'check_current_password' => 'The current password is incorrect.'
                    ]
                ],
                'new_password' => [
                    'rules' => 'required|min_length[5]|max_length[20]|is_password_strong[new_password]',
                    'errors' => [
                        'required' => 'New password is required.',
                        'min_length' => 'New password must have at least 5 characters.',
                        'max_length' => 'New password must not exceed more than 20 characters.',
                        'is_password_strong' => 'The password must contain at least 1 uppercase, 1 lowercase, 1 number and 1 special character.'
                    ]
                ],
                'confirm_new_password' => [
                    'rules' => 'required|matches[new_password]',
                    'errors' => [
                        'required' => 'Confirm new password.',
                        'matches' => 'Password does not match.'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                // Update user (admin) password in DB
                $user->where('id', $user_info->id)->set(['password' => Hash::make($request->getVar('new_password'))])->update();

                // Send email notification to user (admin) email address
                $mail_data = array(
                    'user' => $user_info,
                    'new_password' => $request->getVar('new_password')
                );
        
                $view = \Config\Services::renderer();
                $mail_body = $view->setVar('mail_data', $mail_data)->render('email-templates/password-changed-email-template');
        
                $mailConfig = array(
                    'mail_from_email' => env('EMAIL_FROM_ADDRESS'),
                    'mail_from_name' => env('EMAIL_FROM_NAME'),
                    'mail_recipient_email' => $user_info->email,
                    'mail_recipient_name' => $user_info->name,
                    'mail_subject' => 'Password Changed',
                    'mail_body' => $mail_body
                );

                sendEmail($mailConfig);
                return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Done! Your password has been successfully updated.']);
            }
        }
    }

    public function settings()
    {
        $data = [
            'pageTitle' => 'Settings'
        ];
        return view('backend/pages/settings', $data);
    }

    public function updateGeneralSettings()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $validation = \Config\Services::validation();

            $this->validate([
                'blog_title' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Blog title is required.'
                    ]
                ],
                'blog_email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Blog email is required.',
                        'valid_email' => 'Invalid email address.'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $settings = new Setting();
                $setting_id = $settings->asObject()->first()->id;
                $update = $settings->where('id', $setting_id)->set([
                                'blog_title' => $request->getVar('blog_title'),
                                'blog_email' => $request->getVar('blog_email'),
                                'blog_phone' => $request->getVar('blog_phone'),
                                'blog_meta_keywords' => $request->getVar('blog_meta_keywords'),
                                'blog_meta_description' => $request->getVar('blog_meta_description')
                            ])->update();

                if ( $update ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'General settings have been updated successfully.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong.']);
                } 
            } 
        }
    }

    public function updateBlogLogo()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $settings = new Setting();
            $path = 'images/blog/';
            $file = $request->getFile('blog_logo');
            $settings_data = $settings->asObject()->first();
            $old_blog_logo = $settings_data->blog_logo;
            $new_filename = 'TaxBlog_logo' . $file->getRandomName();

            if ( $file->move($path, $new_filename) ) {

                if ( $old_blog_logo != null && file_exists($path . $old_blog_logo) ) {
                    unlink($path . $old_blog_logo);
                }
                $update = $settings->where('id', $settings_data->id)->set(['blog_logo' => $new_filename])->update();

                if ( $update ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Done! Tax Management logo has been successfully updated.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong on updating new logo info.']);
                }
                
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong on uploading new logo.']);
            }
        } 
    }

    public function updateBlogFavicon()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $settings = new Setting();
            $path = 'images/blog/';
            $file = $request->getFile('blog_favicon');
            $settings_data = $settings->asObject()->first();
            $old_blog_favicon = $settings_data->blog_favicon;
            $new_filename = 'Tax_favicon_' . $file->getRandomName();

            if ( $file->move($path, $new_filename) ) {

                if ( $old_blog_favicon != null && file_exists($path . $old_blog_favicon) ) {
                    unlink($path . $old_blog_favicon);
                }
                $update = $settings->where('id', $settings_data->id)->set(['blog_favicon' => $new_filename])->update();

                if ( $update ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Done! Tax Management favicon has been successfully updated.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong on updating new favicon file.']);
                }
                
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong on uploading new favicon file.']);
            }
        } 
    }

    public function updateSocialMedia()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $validation = \Config\Services::validation();
            $this->validate([
                'facebook_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid facebook page URL.'
                    ]
                ],
                'twitter_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid twitter URL.'
                    ]
                ],
                'instagram_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid instagram URL.'
                    ]
                ],
                'youtube_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid YouTube channel URL.'
                    ]
                ],
                'github_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid GitHub URL.'
                    ]
                ],
                'linkedin_url' => [
                    'rules' => 'permit_empty|valid_url_strict',
                    'errors' => [
                        'valid_url_strict' => 'Invalid LinkedIn URL.'
                    ]
                ],
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $social_media = new SocialMedia();
                $social_media_id = $social_media->asObject()->first()->id;
                $update = $social_media->where('id', $social_media_id)->set([
                                'facebook_url' => $request->getVar('facebook_url'),
                                'twitter_url' => $request->getVar('twitter_url'),
                                'instagram_url' => $request->getVar('instagram_url'),
                                'youtube_url' => $request->getVar('youtube_url'),
                                'github_url' => $request->getVar('github_url'),
                                'linkedin_url' => $request->getVar('linkedin_url'),
                            ])->update();

                if ( $update ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Done! Tax Management social media has been successfully updated.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong on updating social media.']);
                }    
            }
        }
    }

    public function categories()
    {
        $data = [
            'pageTitle' => 'Categories'
        ];
        return view('backend/pages/categories', $data);
    }

    public function addCategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $validation = \Config\Services::validation();

            $this->validate([
                'category_name' => [
                    'rules' => 'required|is_unique[categories.name]',
                    'errors' => [
                        'required' => 'Category name is required.',
                        'is_unique' => 'Category name already exists.'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $category = new Category();
                $save = $category->save(['name' => $request->getVar('category_name')]);

                if ( $save ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'New category has been successfully added.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong.']);
                } 
            }
        }
    }

    public function getCategories()
    {
        //DB details
        $dbDetails = array(
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db"   => $this->db->database
        );

        $table = "categories";
        $primaryKey = "id";
        $columns = array(
            array(
                "db" => "id",
                "dt" => 0
            ),
            array(
                "db" => "name",
                "dt" => 1
            ),
            array(
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($d, $row) {
                    // return "(x) will be added later.";
                    $subcategory = new Subcategory();
                    $subcategories = $subcategory->where(['parent_cat' => $row['id']])->findAll();
                    return count($subcategories);
                }
            ),
            array(
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($d, $row) {
                    return "<div class='btn-group'>
                                <button class='btn btn-sm btn-link p-0 mx-1 editCategoryBtn' data-id='" . $row['id'] . "'>Edit</button>
                                <button class='btn btn-sm btn-link p-0 mx-1 deleteCategoryBtn' data-id='" . $row['id'] . "'>Delete</button>
                            </div>";
                }
            ),
            array(
                "db" => "ordering",
                "dt" => 4
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function getCategory() 
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('category_id');
            $category = new Category();
            $category_data = $category->find($id);
            return $this->response->setJSON(['data' => $category_data]);
        }
    }

    public function updateCategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('category_id');
            $validation = \Config\Services::validation();

            $this->validate([
                'category_name' => [
                    'rules' => 'required|is_unique[categories.name,id,' . $id . ']',
                    'errors' => [
                        'required' => 'Category name is required.',
                        'is_unique' => 'Category name already exists.'
                    ]
                ]
            ]);
        }

        if ( $validation->run() == FALSE ) {
            $errors = $validation->getErrors();
            return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
        } else {
            $category = new Category();
            $update = $category->where('id', $id)->set(['name'=>$request->getVar('category_name')])->update();

            if ( $update ) {
                return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Category has been successfully updated.']);
            } else {
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong.']);
            }
        }
    }

    public function deleteCategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('category_id');
            $category = new Category();

            // Check if it's related to subcategories: in future video
            // Check if it's related to posts through subcategories: in future video

            // Delete category
            $delete = $category->delete($id);

            if ( $delete ) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Category has been successfully deleted.']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Something went wrong.']);
            }
        }
    }

    public function reorderCategories()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $positions = $request->getVar('positions');
            $category = new Category();

            foreach ($positions as $position) {
                $index = $position[0];
                $newPosition = $position[1];
                $category->where('id', $index)->set(['ordering' => $newPosition])->update();
            }
            return $this->response->setJSON(['status' => 1, 'msg' => 'Categories ordering has been successfully updated.']);
        }
    }

    public function getParentCategories()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('parent_category_id');
            $options = '<option value="0">Uncategorized</option>';
            $category = new Category();
            $parent_categories = $category->findAll();

            if ( count($parent_categories) ) {
                $added_options = '';
                foreach ($parent_categories as $parent_category) {
                    $isSelected = $parent_category['id'] == $id ? 'selected' : '';
                    $added_options.='<option value="' . $parent_category['id'] . '" ' . $isSelected . '>' . $parent_category['name'] . '</option>';
                }
                $options = $options . $added_options;
                return $this->response->setJSON(['status' => 1, 'data' => $options]);
            } else {
                return $this->response->setJSON(['status' => 1, 'data' => $options]);
            }
        }
    }

    public function addSubcategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $validation = \Config\Services:: validation();

            $this->validate([
                'subcategory_name' => [
                    'rules' => 'required|is_unique[subcategories.name]',
                    'errors' => [
                        'required' => 'Subcategory name is required.',
                        'is_unique' => 'Subcategory name already exists.'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $subcategory = new Subcategory();
                $subcategory_name = $request->getVar('subcategory_name');
                $subcategory_description = $request->getVar('description');
                $subcategory_parent_category = $request->getVar('parent_cat');
                $subcategory_slug = Slugify::model(Subcategory::class)->make($subcategory_name);

                $save = $subcategory->save([
                    'name' => $subcategory_name,
                    'parent_cat' => $subcategory_parent_category,
                    'slug' => $subcategory_slug,
                    'description' => $subcategory_description
                ]);

                if ( $save ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'New subcategory has been added.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong.']);
                }
                
            }
        }
    }

    public function getSubcategories()
    {
        $category = new Category();
        $subcategory = new Subcategory();

        // DB details
        $dbDetails = array(
            "host"  => $this->db->hostname,
            "user"  => $this->db->username,
            "pass"  => $this->db->password,
            "db"    => $this->db->database
        );

        $table = "subcategories";
        $primaryKey = "id";
        $columns = array(
            array(
                "db" => "id",
                "dt" => 0
            ),
            array(
                "db" => "name",
                "dt" => 1
            ),
            array(
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($d, $row) use ($category, $subcategory) {
                    $parent_cat_id = $subcategory->asObject()->where("id", $row['id'])->first()->parent_cat;
                    $parent_cat_name = ' --- ';
                    if ( $parent_cat_id != 0) {
                        $parent_cat_name = $category->asObject()->where("id", $parent_cat_id)->first()->name;
                    }
                    return $parent_cat_name;
                }
            ),
            array(
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($d, $row) {
                    return "(x) will be added later.";
                }
            ),
            array(
                "db" => "id",
                "dt" => 4,
                "formatter" => function ($d, $row) {
                    return "<div class='btn-group'>
                                <button class='btn btn-sm btn-link p-0 mx-1 editSubcategoryBtn' data-id='" . $row['id'] . "'>Edit</button>
                                <button class='btn btn-sm btn-link p-0 mx-1 deleteSubcategoryBtn' data-id='" . $row['id'] . "'>Delete</button>
                            </div>";
                }
            ),
            array(
                "db" => "ordering",
                "dt" => 5
            )
        );

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function getSubcategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('subcategory_id');
            $subcategory = new Subcategory();
            $subcategory_data = $subcategory->find($id);
            return $this->response->setJSON(['data' => $subcategory_data]);
        }
    }

    public function updateSubcategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('subcategory_id');
            $validation = \Config\Services::validation();

            $this->validate([
                'subcategory_name' => [
                    'rules' => 'required|is_unique[subcategories.name,id,' . $id . ']',
                    'errors' => [
                        'required' => 'Subcategory name is required.',
                        'is_unique' => 'Subcategory name already exists.'
                    ]
                ]
            ]);

            if ( $validation->run() == FALSE ) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'error' => $errors]);
            } else {
                $subcategory = new Subcategory();
                $data = array(
                    'name' => $request->getVar('subcategory_name'),
                    'parent_cat' => $request->getVar('parent_cat'),
                    'description' => $request->getVar('description')
                );
                $update = $subcategory->update($id, $data);

                if ( $update ) {
                    return $this->response->setJSON(['status' => 1, 'token' => csrf_hash(), 'msg' => 'Subcategory has been successfully updated.']);
                } else {
                    return $this->response->setJSON(['status' => 0, 'token' => csrf_hash(), 'msg' => 'Something went wrong.']);
                }
            }
        }
    }

    public function reorderSubcategories()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $positions = $request->getVar('positions');
            $subcategory = new Subcategory();

            foreach ($positions as $position) {
                $index = $position[0];
                $newPosition = $position[1];
                $subcategory->where('id', $index)->set(['ordering' => $newPosition])->update();
            }

            return $this->response->setJSON(['status' => 1, 'msg' => 'Subcategories ordering has been successfully updated.']);
        }
    }

    public function deleteSubcategory()
    {
        $request = \Config\Services::request();

        if ( $request->isAJAX() ) {
            $id = $request->getVar('subcategory_id');
            $subcategory = new Subcategory();

            // Check related posts

            // Delete subcategory
            $delete = $subcategory->delete($id);

            if ( $delete ) {
                return $this->response->setJSON(['status' => 1, 'msg' => 'Subcategory has been successfully deleted.']);
            } else {
                return $this->response->setJSON(['status' => 0, 'msg' => 'Something went wrong.']);
            }
        }
    }

}
