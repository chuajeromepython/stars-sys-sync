<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ChiefSGOD extends Model implements Auditable
{
    use HasFactory;    
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'tbl_chief_sgods';
    public $timestamps = false;
    public $remember_token = false;                              
}
