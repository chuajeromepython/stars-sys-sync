<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class AppApiController extends Controller
{
    public function authenticate($username, $password){

        $credentials = array(
            "username" => $username,
            "password" => $password
        );
        $result = array(
            "status" => 0,
            "data" => null,
            "message" => "Not Found"
        );
        if(Auth::attempt( $credentials ) ) {
            $result['data'] = Auth::user();
            $result['status'] = "200";
            $result['message'] = "Successfully login";
        }

        return response()->json($result);

    }
}
