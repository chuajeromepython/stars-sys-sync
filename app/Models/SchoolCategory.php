<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolCategory extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_school_categories';
}
