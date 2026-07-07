<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;w

    protected $table = 'tbl_classrooms';

    public $timestamps = false;

    public $remember_token = false;

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class, 'classroom_id', 'id');
    }

    public function advisoryTeacherClass(): HasOne
    {
        return $this->hasOne(TeacherClass::class, 'classroom_id', 'id')->where('advisory', 1);
    }
}
