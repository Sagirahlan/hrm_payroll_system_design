<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditTrail;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'Login',
                'description' => 'Login Successful',
                'action_timestamp' => now(),
                'entity_type' => 'User', // Required field to avoid SQL error
                'entity_id' => Auth::id(), // Optional, tracks affected entity
            ]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
       AuditTrail::create([
    'user_id' => auth()->id(),
    'action' => 'Logout',
    'description' => 'Logout Successful',
    'action_timestamp' => now(),
    'entity_type' => 'User', // <-- pass this
    'entity_id' => auth()->id(), // optional, if tracking what entity was affected
]);


        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}