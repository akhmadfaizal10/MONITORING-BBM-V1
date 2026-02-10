<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetPerusahaan;

class UserBudgetController extends Controller
{
    /**
     * GET /user/budget
     * Tampilkan semua budget milik company user
     */
    public function index()
    {
        $user = Auth::user();

        $budgets = BudgetPerusahaan::where('company', $user->company)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('user.budget.index', [
            'budgets' => $budgets,
            'company' => $user->company
        ]);
    }

    /**
     * GET /user/budget/create
     * Tampilkan form tambah budget
     */
    public function create()
    {
        return view('user.budget.create');
    }

    /**
     * POST /user/budget
     * Simpan budget baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'month'         => ['required'],
            'year'          => ['required', 'digits:4'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
        ]);

        BudgetPerusahaan::create([
            // 🔒 company TIDAK dari form
            'company'        => Auth::user()->company,
            'month'          => $request->month,
            'year'           => $request->year,
            'budget_amount'  => $request->budget_amount,
        ]);

        return redirect()
            ->route('user.budget.index')
            ->with('success', 'Budget berhasil disimpan');
    }

    /**
     * GET /user/budget/{id}/edit
     * Tampilkan form edit budget
     */
    public function edit($id)
    {
        $user = Auth::user();

        $budget = BudgetPerusahaan::where('id', $id)
            ->where('company', $user->company) // 🔒 proteksi akses
            ->firstOrFail();

        return view('user.budget.edit', compact('budget'));
    }

    /**
     * PUT /user/budget/{id}
     * Update budget
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'month'         => ['required'],
            'year'          => ['required', 'digits:4'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();

        $budget = BudgetPerusahaan::where('id', $id)
            ->where('company', $user->company)
            ->firstOrFail();

        $budget->update([
            'month'         => $request->month,
            'year'          => $request->year,
            'budget_amount' => $request->budget_amount,
        ]);

        return redirect()
            ->route('user.budget.index')
            ->with('success', 'Budget berhasil diupdate');
    }

    /**
     * DELETE /user/budget/{id}
     * Hapus budget
     */
    public function destroy($id)
    {
        $user = Auth::user();

        BudgetPerusahaan::where('id', $id)
            ->where('company', $user->company)
            ->delete();

        return redirect()
            ->route('user.budget.index')
            ->with('success', 'Budget berhasil dihapus');
    }
}
