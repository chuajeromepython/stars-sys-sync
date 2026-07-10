<?php

namespace App\Http\Controllers;

use App\Models\UploaderCellPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UploaderCellPositionController extends Controller
{
    public function index()
    {
        $page = [
            'name' => 'Uploader Cell Position',
            'title' => 'Uploader Cell Position Settings',
            'crumb' => ['Uploader Cell Position' => '/uploader-setting'],
        ];

        $uploaderCellPositions = UploaderCellPosition::all()
            ->load('school')
            ->has('user', Auth::user()->id);

        return view('uploader_setting.index', compact('uploaderCellPositions', 'page'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|string',
            'academic_year' => 'required|string',
            'grade_level' => 'required|string',
            'section' => 'required|string',
            'total' => 'required|string',
            'strand' => 'nullable|string',
            'lrn' => 'required|string',
            'name' => 'required|string',
            'gender' => 'required|string',
            'birth_date' => 'required|string',
            'start' => 'required|integer',
            'is_shs' => 'boolean',
            'is_active' => 'boolean',
        ]);
    }
}
