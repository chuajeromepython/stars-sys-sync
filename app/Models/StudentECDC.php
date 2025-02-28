<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StudentECDC extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = "tbl_student_ecdcs";

    public static function scopeGetECD($query, $filter): void
    {
        $query->select()
        ->join('tbl_ecdcs', 'tbl_student_ecdcs.ecdc_id', 'tbl_ecdcs.id')
        ->where($filter);
            
    }
}
