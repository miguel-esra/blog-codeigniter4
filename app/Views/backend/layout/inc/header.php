<div class="header">
    <div class="header-left">
        <div class="menu-icon bi bi-list"></div>
        <div
            class="search-toggle-icon bi bi-search"
            data-toggle="header_search"
        ></div>
        <div class="header-search">
            <form>
                <div class="form-group mb-0">
                    <i class="dw dw-search2 search-icon"></i>
                    <input
                        type="text"
                        class="form-control search-input"
                        placeholder="Search Here"
                    />
                    <div class="dropdown">
                        <a
                            class="dropdown-toggle no-arrow"
                            href="#"
                            role="button"
                            data-toggle="dropdown"
                        >
                            <i class="ion-arrow-down-c"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label"
                                    >From</label
                                >
                                <div class="col-sm-12 col-md-10">
                                    <input
                                        class="form-control form-control-sm form-control-line"
                                        type="text"
                                    />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">To</label>
                                <div class="col-sm-12 col-md-10">
                                    <input
                                        class="form-control form-control-sm form-control-line"
                                        type="text"
                                    />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label"
                                    >Subject</label
                                >
                                <div class="col-sm-12 col-md-10">
                                    <input
                                        class="form-control form-control-sm form-control-line"
                                        type="text"
                                    />
                                </div>
                            </div>
                            <div class="text-right">
                                <button class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="header-right">
        <div class="dashboard-setting user-notification">
            <div class="dropdown">
                <a
                    class="dropdown-toggle no-arrow"
                    href="javascript:;"
                    data-toggle="right-sidebar"
                >
                    <i class="dw dw-settings2"></i>
                </a>
            </div>
        </div>
        <div class="user-notification">
            <div class="dropdown">
                <a
                    class="dropdown-toggle no-arrow"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                >
                    <i class="icon-copy dw dw-notification"></i>
                    <span class="badge notification-active"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="notification-list mx-h-350 customscroll">
                        <ul>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/img.jpg" alt="" />
                                    <h3>John Doe</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/photo1.jpg" alt="" />
                                    <h3>Lea R. Frith</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/photo2.jpg" alt="" />
                                    <h3>Erik L. Richards</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/photo3.jpg" alt="" />
                                    <h3>John Doe</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/photo4.jpg" alt="" />
                                    <h3>Renee I. Hansen</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="../backend/vendors/images/img.jpg" alt="" />
                                    <h3>Vicki M. Coleman</h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing
                                        elit, sed...
                                    </p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img src="<?= get_user()->picture == null ? '/images/users/default-avatar.png' : '/images/users/' . get_user()->picture ?>" alt="" class="ci-avatar-photo" />
                    </span>
                    <span class="user-name ci-user-name"><?= get_user()->name ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="<?= route_to('admin.profile'); ?>">
                        <i class="dw dw-user1"></i> Profile
                    </a>
                    <a class="dropdown-item" href="<?= route_to('settings'); ?>">
                        <i class="dw dw-settings2"></i> Settings
                    </a>
                    <a class="dropdown-item" href="faq.html">
                        <i class="dw dw-help"></i> Help
                    </a>
                    <a class="dropdown-item" href="<?= route_to('admin.logout') ?>">
                        <i class="dw dw-logout"></i> Log Out 
                    </a>
                </div>
            </div>
        </div>
        <div class="github-link">
            <a href="https://github.com/dropways/deskapp" target="_blank"
                ><img src="../backend/vendors/images/github.svg" alt=""
            /></a>
        </div>
    </div>
</div>