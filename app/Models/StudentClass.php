<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StudentClass extends Model implements Auditable
{
    use HasFactory;  
    use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_student_classes';
    public $timestamps = false;
    public $remember_token = false;
}
