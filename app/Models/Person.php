<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory;
    use SoftDeletes;

    // use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_persons';

    public $timestamps = false;

    public $remember_token = false;

    public function user()
    {
        return $this->hasOne(User::class, 'person_id', 'id');
    }
}
