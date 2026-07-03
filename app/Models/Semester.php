<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    public $remember_token = false;

    protected $table = 'tbl_semesters';
}
