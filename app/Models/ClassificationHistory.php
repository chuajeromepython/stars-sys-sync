<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationHistory extends Model
{
    use HasFactory;

    // use \OwenIt\Auditing\Auditable;
    protected $table = 'tbl_classification_histories';
}
