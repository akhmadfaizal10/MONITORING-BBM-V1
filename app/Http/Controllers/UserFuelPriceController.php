<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NikFuelPrice;

class UserFuelPriceController extends Controller
{
    /**
     * GET /user/nikfuel
     * Tampilkan harga fuel per NIK milik company user
     */
    public function index()
    {
        $user = Auth::user();

        $prices = NikFuelPrice::where('company', $user->company)
            ->orderBy('nik')
            ->get();

        return view('user.nikfuel.index', [
            'prices'  => $prices,
            'company' => $user->company,
        ]);
    }

    /**
     * GET /user/nikfuel/create
     * Form tambah harga fuel
     */
    public function create()
    {
        return view('user.nikfuel.create');
    }

    /**
     * POST /user/nikfuel
     * Simpan / update harga fuel per NIK
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik'            => ['required', 'string'],
            'price_per_liter'=> ['required', 'numeric', 'min:0'],
        ]);

        NikFuelPrice::updateOrCreate(
            [
                // 🔒 KUNCI UNIK: company + nik
                'company' => Auth::user()->company,
                'nik'     => $request->nik,
            ],
            [
                'price_per_liter' => $request->price_per_liter,
            ]
        );

        return redirect()
            ->route('user.nikfuel.index')
            ->with('success', 'Harga fuel berhasil disimpan');
    }

    /**
     * GET /user/nikfuel/{id}/edit
     * Form edit harga fuel
     */
    public function edit($id)
    {
        $user = Auth::user();

        $price = NikFuelPrice::where('id', $id)
            ->where('company', $user->company) // 🔒 proteksi
            ->firstOrFail();

        return view('user.nikfuel.edit', compact('price'));
    }

    /**
     * PUT /user/nikfuel/{id}
     * Update harga fuel
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nik'            => ['required', 'string'],
            'price_per_liter'=> ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();

        $price = NikFuelPrice::where('id', $id)
            ->where('company', $user->company)
            ->firstOrFail();

        $price->update([
            'nik'             => $request->nik,
            'price_per_liter' => $request->price_per_liter,
        ]);

        return redirect()
            ->route('user.nikfuel.index')
            ->with('success', 'Harga fuel berhasil diperbarui');
    }

    /**
     * DELETE /user/nikfuel/{id}
     * Hapus harga fuel
     */
    public function destroy($id)
    {
        $user = Auth::user();

        NikFuelPrice::where('id', $id)
            ->where('company', $user->company)
            ->delete();

        return redirect()
            ->route('user.nikfuel.index')
            ->with('success', 'Data berhasil dihapus');
    }
}
