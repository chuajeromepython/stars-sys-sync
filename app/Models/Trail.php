<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trail extends Model
{
    use HasFactory;

    protected $table = 'audits';

    public static function models()
    {
        $path = app_path().'/Models';
        $results = scandir($path);
        $results = array_slice($results, 2);
        $models = [];

        foreach ($results as $key => $result) {
            $models[] = explode('.', $result)[0];
        }

        return $models;

    }
}
