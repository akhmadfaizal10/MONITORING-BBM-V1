<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BudgetPerusahaan;

class CompanyBudgetController extends Controller
{
    public function index()
    {
        // ❌ jangan pakai latest()
        $budgets = BudgetPerusahaan::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.budget.index', compact('budgets'));
    }

    public function create()
    {
        return view('admin.budget.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company'        => 'required',
            'month'          => 'required',
            'year'           => 'required|digits:4',
            'budget_amount'  => 'required|numeric|min:0',
        ]);

        BudgetPerusahaan::create([
            'company'        => $request->company,
            'month'          => $request->month,
            'year'           => $request->year,
            'budget_amount'  => $request->budget_amount,
        ]);

        return redirect()
            ->route('admin.budget.index')
            ->with('success', 'Budget berhasil ditambahkan');
    }

    public function edit($id)
    {
        $budget = BudgetPerusahaan::findOrFail($id);
        return view('admin.budget.edit', compact('budget'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'month'         => 'required',
        'year'          => 'required|digits:4',
        'budget_amount' => 'required|numeric|min:0',
    ]);

    $budget = BudgetPerusahaan::findOrFail($id);

    $budget->update([
        // company DIKUNCI, tidak dari form
        'company'        => $budget->company,
        'month'          => $request->month,
        'year'           => $request->year,
        'budget_amount'  => $request->budget_amount,
    ]);

    return redirect()
        ->route('admin.budget.index')
        ->with('success', 'Budget berhasil diupdate');
}


    public function destroy($id)
    {
        BudgetPerusahaan::where('id', $id)->delete();
        return back()->with('success', 'Budget dihapus');
    }
}
