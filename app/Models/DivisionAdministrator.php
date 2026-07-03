<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionAdministrator extends Model
{
    use HasFactory;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_division_administrators';

    public $timestamps = false;

    public $remember_token = false;
}
