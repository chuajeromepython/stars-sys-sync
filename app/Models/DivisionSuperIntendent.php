<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DivisionSuperIntendent extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_division_superintendents';

    public $timestamps = false;

    public $remember_token = false;
}
