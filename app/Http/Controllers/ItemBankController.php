<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;

class ItemBankController extends Controller
{
    public function index(){

        $page = [
            'name'      =>  'Item Bank',
            'title'     =>  'Item Bank Management',
            'crumb'     =>  array('Item Banks' => '/users')
        ];

        $academic_years = AcademicYear::orderBy('to')->get();

        return view('item_banks.index', compact(
            'page', 'academic_years'
        ));


    }

}

