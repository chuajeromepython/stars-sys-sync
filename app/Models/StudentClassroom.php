<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class StudentClassroom extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    // STATUS 
    // 1 - FROM SF 1
    // 2 - TRANSFEREE
    
    protected $table = 'tbl_student_classrooms';
    public $timestamps = false;
    public $remember_token = false;
}
