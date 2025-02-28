<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\School;
use App\Models\Competency;

class AuthController extends Controller
{   
    public function welcome(){
        
        $schools = School::count();
        $competencies = Competency::count();
        $users = User::count();
        return view('welcome', compact('schools', 'competencies', 'users'));
    }
    public function index()
    {
        Auth::logout();
        return view('auth.login');
    }
    
    public function authenticate(Request $request)
    {
        $this->validate($request,[
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('username', 'password');

        if(! Auth::attempt( $credentials ) ) {
            return back()->withErrors('Invalid credentials')->withInput($request->all);
        }
        
        return redirect('/dashboard');
    }

    
    public function destroy()
    {
        Auth::logout();
        return redirect('/login');
    }
    
    public function forbidden(){

        return view('auth.403');
    }

    public function account(){

        $page = [
            'name'      =>  'Account',
            'title'     =>  'Account Management',
            'crumb'     =>  array('Account' => '/account')
        ];

        return view('auth.account',
            compact('page')
        );
    }

    public function updatePassword(Request $request){
        // dd($request->password);
        $user = User::find(Auth::user()->id);
        $user->password = bcrypt($request->password);
        $user->save();

        return back()->with('success', "Password has been updated");
    }
}
