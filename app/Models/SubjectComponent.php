<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectComponent extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_subject_components';

    public $timestamps = false;

    public $remember_token = false;

    public function subject()
    {
        return $this->hasOne(Subject::class, 'id', 'subject_id');
    }
}
