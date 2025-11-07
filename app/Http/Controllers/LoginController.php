<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Arahkan sesuai role
            return redirect($user->role === 'admin' ? 'dashboard' : 'dashboard-user');
        }

        return view('login');
    }

    public function actionlogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $request->session()->regenerate();

            // ✅ Redirect sesuai role
            if ($user->role === 'admin') {
                return redirect()->intended('dashboard');
            } else {
                return redirect()->intended('dashboard-user');
            }
        }

        // Gagal login
        Session::flash('error', 'Email atau Password salah');
        return redirect('/')->withInput();
    }

    public function actionlogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
