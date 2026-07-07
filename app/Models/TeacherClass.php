<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    use HasFactory;
    // use \OwenIt\Auditing\Auditable;
    // use SoftDeletes;

    protected $table = 'tbl_teacher_classes';

    public $timestamps = false;

    public $remember_token = false;
}
