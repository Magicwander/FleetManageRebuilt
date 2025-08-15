<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // Find user by email
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            // Login the user
            Auth::login($user);
            
            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect('/dashboard');
            } else {
                return redirect('/customer-dashboard');
            }
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
