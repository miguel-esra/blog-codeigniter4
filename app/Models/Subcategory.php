<?php

namespace App\Models;

use CodeIgniter\Model;

class Subcategory extends Model
{
    protected $table            = 'subcategories';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'slug', 'parent_cat', 'description', 'ordering'
    ];
}
