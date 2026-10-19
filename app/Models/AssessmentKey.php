<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentKey extends Model
{
    use HasFactory;
    use SoftDeletes;

    // use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_assessment_keys';

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }

    public function questions()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
