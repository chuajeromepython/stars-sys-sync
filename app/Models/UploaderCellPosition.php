<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploaderCellPosition extends Model
{
    use HasFactory;

    protected $table = 'tbl_uploader_cell_positions';

    protected $fillable = [
        'school_id',
        'academic_year',
        'grade_level',
        'section',
        'total',
        'strand',
        'lrn',
        'name',
        'gender',
        'birth_date',
        'start',
        'is_shs',
        'is_active',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
