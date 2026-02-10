<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NikFuelPrice;

class NikFuelPriceController extends Controller
{
    public function index()
    {
        $prices = NikFuelPrice::latest()->get();
        return view('admin.nikfuel.index', compact('prices'));
    }

    public function create()
    {
        return view('admin.nikfuel.create');
    }

    public function store(Request $request)
    {
        NikFuelPrice::create($request->all());

        return redirect()
            ->route('admin.nikfuel.index')
            ->with('success','Harga fuel berhasil ditambahkan');
    }

    public function edit($id)
    {
        $price = NikFuelPrice::findOrFail($id);
        return view('admin.nikfuel.edit', compact('price'));
    }

    public function update(Request $request, $id)
    {
        $price = NikFuelPrice::findOrFail($id);
        $price->update($request->all());

        return redirect()
            ->route('admin.nikfuel.index')
            ->with('success','Harga fuel diperbarui');
    }

    public function destroy($id)
    {
        NikFuelPrice::destroy($id);
        return back()->with('success','Data dihapus');
    }
}
