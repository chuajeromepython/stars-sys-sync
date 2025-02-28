<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class AcademicYear extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    public $timestamps = false;
    public $remember_token = false;
    protected $table = 'tbl_academic_years';

    public static function active(){
        $academic_year = AcademicYear::where('is_active', 1)->first();
        return $academic_year;
    }
}
