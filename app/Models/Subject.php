<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Subject extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'tbl_subjects';
    public $timestamps = false;
    public $remember_token = false;


    public function subject_components()
    {
        return $this->hasMany(SubjectComponent::class, 'subject_id', 'id');
    }
}
