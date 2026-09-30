<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;
    // use \OwenIt\Auditing\Auditable;

    protected $table = 'tbl_students';

    public $timestamps = false;

    public $remember_token = false;

    public static function status($status_id)
    {

        switch ($status_id) {
            case '1':
                $status = 'Regular';
                break;
            case '2':
                $status = 'Trasferee';
                break;
            case '4':
                $status = 'Drop';
                break;
            case '3':
                $status = 'Back Subject';
            case '0':
                $status = 'Exclude';
                break;
            default:
                // code...
                break;
        }

        return $status;
    }

    public static function getStatusOptions()
    {
        $result = [
            '1' => 'Regular',
            '2' => 'Trasferee',
            '3' => 'Back Subject',
            '4' => 'Drop',
            '0' => 'Exclude',
        ];

        return $result;
    }

    public function studentScores()
    {
        return $this->hasMany(StudentScore::class, 'student_id');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'student_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
