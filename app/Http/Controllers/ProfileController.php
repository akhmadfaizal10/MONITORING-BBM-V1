<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'password' => 'nullable|min:6',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update basic info
        $user->name = $request->name;
        $user->company = $request->company;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Upload foto jika ada
        if ($request->hasFile('photo')) {
            $filename = time() . '.' . $request->photo->extension();
            $request->photo->move(public_path('profile_photos'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
