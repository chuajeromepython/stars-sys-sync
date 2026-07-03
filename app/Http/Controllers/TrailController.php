<?php

namespace App\Http\Controllers;

use App\Models\Trail;

class TrailController extends Controller
{
    public function index()
    {
        $page = [
            'name' => 'Trail',
            'title' => 'Audit Trail Management',
            'crumb' => ['Trail' => '/tracks'],
        ];

        $models = Trail::models();
        $model = "App\Models\User";
        $trails = Trail::where('auditable_type', $model)
            ->join('tbl_users', 'audits.user_id', 'tbl_users.id')
            ->get();

        return view('trails.index', compact(
            'page',
            'models', 'trails'
        ));
    }

    public function getTrails($model)
    {
        $model = "App\Models\ ".$model;
        $model = str_replace(' ', '', $model);
        $trails = Trail::where('auditable_type', $model)
            ->select(
                'audits.*',
                'username'
            )
            ->join('tbl_users', 'audits.user_id', 'tbl_users.id')
            ->get();

        return $trails;

    }
}
