<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\School;
use App\Models\User;
use App\Services\QrAuthorizationPayloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private QrAuthorizationPayloadService $qrAuthorizationPayloadService) {}

    public function welcome()
    {

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
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('username', 'password');

        if (! Auth::attempt($credentials)) {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Invalid credentials'])->withInput($request->all);
        }

        return redirect('/dashboard');
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/login');
    }

    public function forbidden()
    {

        return view('auth.403');
    }

    public function account()
    {

        $page = [
            'name' => 'Account',
            'title' => 'Account Management',
            'crumb' => ['Account' => '/account'],
        ];

        $qrData = $this->qrAuthorizationPayloadService->buildForUser(Auth::user());

        return view('auth.account', compact('page', 'qrData'));
    }

    public function accountQr()
    {
        $qrData = $this->qrAuthorizationPayloadService->buildForUser(Auth::user());

        return response()->json([
            'qr_svg' => $qrData['qr_svg'],
            'payload' => $qrData['payload'],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password has been updated');
    }
}
