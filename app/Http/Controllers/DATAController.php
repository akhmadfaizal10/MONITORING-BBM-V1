<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class DATAController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'admin') {
            $tables = DB::select('SHOW TABLES');
            $companies = [];
            foreach ($tables as $table) {
                $name = array_values((array)$table)[0];
                if (str_starts_with($name, 'company_') && !str_contains($name, '_k')) {
                    $companies[] = [
                        'name' => ucwords(str_replace('_', ' ', str_replace('company_', '', $name))),
                        'table' => $name
                    ];
                }
            }
            return view('data', compact('companies', 'role'));
        }

        return redirect('/');
    }
    // API: Ambil daftar perusahaan
    public function companies()
    {
        $tables = DB::select('SHOW TABLES');
        $companies = [];

        foreach ($tables as $table) {
            $name = array_values((array)$table)[0];
            if (str_starts_with($name, 'company_') && !str_contains($name, '_k')) {
                $companies[] = [
                    'name' => ucwords(str_replace('_', ' ', str_replace('company_', '', $name))),
                    'table' => $name
                ];
            }
        }

        return response()->json($companies);
    }

    // API: Ambil tabel per NIK dalam perusahaan
    public function companyTables(Request $request)
    {
        $company = $request->query('company');
        $tables = DB::select("SHOW TABLES LIKE '{$company}_%'");
        return response()->json(array_map(fn($t) => array_values((array)$t)[0], $tables));
    }

    // API: Ambil data dari tabel tertentu
    public function tableData(Request $request)
    {
        $table = $request->query('table');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 50);
        $search = $request->query('search', '');

        if (!Schema::hasTable($table)) {
            return response()->json(['data' => [], 'total' => 0]);
        }

        $query = DB::table($table);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('vehicle_id', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->offset(($page - 1) * $perPage)
                      ->limit($perPage)
                      ->orderBy('id', 'desc')
                      ->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    // API: Export semua data tabel
    public function export(Request $request)
    {
        $table = $request->query('table');
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }
        $data = DB::table($table)->get();
        return response()->json($data);
    }

    // 🔥 Hapus semua data dari tabel
    public function clearTable(Request $request)
    {
        $table = $request->query('table');
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }
        DB::table($table)->truncate();
        return response()->json(['message' => "Semua data di tabel {$table} berhasil dihapus"]);
    }

    // 🔥 Hapus tabel perusahaan/nik tertentu
    public function deleteTable(Request $request)
    {
        $table = $request->query('table');
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }
        Schema::drop($table);
        return response()->json(['message' => "Tabel {$table} berhasil dihapus"]);
    }

    // 🔥 Hapus semua data perusahaan (company + semua tabel per NIK)
    public function deleteCompany(Request $request)
    {
        $companyTable = $request->query('company');
        if (!Schema::hasTable($companyTable)) {
            return response()->json(['error' => 'Company table not found'], 404);
        }

        // hapus tabel perusahaan
        Schema::drop($companyTable);

        // hapus tabel per nik
        $tables = DB::select("SHOW TABLES LIKE '{$companyTable}_%'");
        foreach ($tables as $t) {
            $name = array_values((array)$t)[0];
            Schema::dropIfExists($name);
        }

        return response()->json(['message' => "Semua tabel untuk {$companyTable} berhasil dihapus"]);
    }
}