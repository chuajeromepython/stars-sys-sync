<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    // use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_questions';

    protected $guarded = [];

    public function assessmentKey()
    {
        return $this->hasMany(AssessmentKey::class, 'questions_id', 'id');
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class, 'competency_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id', 'id');
    }
}
