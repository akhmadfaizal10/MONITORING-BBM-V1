<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class UserDATAController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Halaman utama
    public function index()
    {
        $user = Auth::user();
        if (!$user->company) {
            abort(403, 'Akun Anda belum terkait dengan perusahaan.');
        }
        return view('data-user');
    }

    // API: Nama perusahaan
    public function company()
    {
        $user = Auth::user();
        $companyTable = strtolower(trim($user->company ?? ''));
        $companyName = ucwords(str_replace('_', ' ', $companyTable));

        return response()->json([
            'name' => $companyName,
            'table' => $companyTable
        ]);
    }

    // API: Daftar tabel NIK (FIX: gunakan binding + cek _k)
    public function companyTables()
    {
        $user = Auth::user();
        $companyTable = strtolower(trim($user->company ?? ''));

        if (!$companyTable || !Schema::hasTable($companyTable)) {
            return response()->json([]);
        }

        // Gunakan binding untuk keamanan
        $pattern = "{$companyTable}_%";
        $tables = DB::select("SHOW TABLES LIKE ?", [$pattern]);

        $result = [];
        foreach ($tables as $t) {
            $name = array_values((array)$t)[0];
            // Pastikan tabel berakhiran _k (untuk NIK)
            if (str_ends_with($name, '_k')) {
                $nik = str_replace("{$companyTable}_", '', $name);
                $result[] = [
                    'nik' => $nik,
                    'table' => $name
                ];
            }
        }

        return response()->json($result);
    }

    // API: Ambil data
    public function tableData(Request $request)
    {
        $user = Auth::user();
        $table = $request->query('table');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 50);
        $search = $request->query('search', '');

        // Validasi tabel milik user
        $companyPrefix = strtolower(trim($user->company ?? '')) . '_';
        if (!$table || !str_starts_with($table, $companyPrefix)) {
            return response()->json(['data' => [], 'total' => 0], 403);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['data' => [], 'total' => 0]);
        }

        $query = DB::table($table);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('vehicle_id', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $data = $query->offset(($page - 1) * $perPage)
                      ->limit($perPage)
                      ->orderBy('id', 'desc')
                      ->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    // API: Export
    public function export(Request $request)
    {
        $user = Auth::user();
        $table = $request->query('table');
        $companyPrefix = strtolower(trim($user->company ?? '')) . '_';

        if (!$table || !str_starts_with($table, $companyPrefix)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $data = DB::table($table)->get();
        return response()->json($data);
    }

    // HAPUS SATU BARIS
    public function deleteRow(Request $request)
    {
        $user = Auth::user();
        $table = $request->input('table');
        $id = $request->input('id');
        $companyPrefix = strtolower(trim($user->company ?? '')) . '_';

        if (!$table || !str_starts_with($table, $companyPrefix) || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deleted = DB::table($table)->where('id', $id)->delete();

        return $deleted
            ? response()->json(['message' => 'Data berhasil dihapus'])
            : response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    // HAPUS SEMUA DATA DI TABEL
    public function clearTable(Request $request)
    {
        $user = Auth::user();
        $table = $request->input('table');
        $companyPrefix = strtolower(trim($user->company ?? '')) . '_';

        if (!$table || !str_starts_with($table, $companyPrefix) || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::table($table)->truncate();
        return response()->json(['message' => "Semua data di {$table} berhasil dihapus"]);
    }
}