<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TeacherClass extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    // use SoftDeletes;
    
    protected $table = 'tbl_teacher_classes';
    public $timestamps = false;
    public $remember_token = false;
}
