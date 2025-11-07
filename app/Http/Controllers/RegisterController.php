<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Session;

class RegisterController extends Controller
{
    public function register()
    {
        return view('register');
    }

    public function actionregister(Request $request)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'company' => 'required|string|max:255',
            'password' => 'required|min:6',
        ]);

        // Simpan data ke database
      User::create([
    'name' => $request->name,
    'email' => $request->email,
    'company' => $request->company,
    'password' => Hash::make($request->password),
    'role' => $request->role ?? 'user',
    'status' => 0,

]);

        // Flash message
        Session::flash('message', 'Registrasi berhasil! Silakan login menggunakan email dan password Anda.');
        return redirect('register');
    }
}