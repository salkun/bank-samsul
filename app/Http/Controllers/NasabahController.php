<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NasabahImport;
use App\Exports\NasabahTemplateExport;

class NasabahController extends Controller
{
    public function index()
    {
        $nasabah = User::where('role', 'nasabah')->get();
        return response()->json($nasabah);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'saldo' => 0,
            'poin' => 0,
            'role' => 'nasabah'
        ]);

        return response()->json(['status' => 'success', 'data' => $user]);
    }

    public function show($id)
    {
        $nasabah = User::where('role', 'nasabah')->findOrFail($id);
        return response()->json($nasabah);
    }

    public function update(Request $request, $id)
    {
        $nasabah = User::where('role', 'nasabah')->findOrFail($id);

        $nasabah->update([
            'nama' => $request->nama ?? $nasabah->nama,
            'username' => $request->username ?? $nasabah->username,
        ]);

        if ($request->filled('password')) {
            $nasabah->password = Hash::make($request->password);
            $nasabah->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data nasabah diperbarui']);
    }

    public function destroy($id)
    {
        $nasabah = User::where('role', 'nasabah')->findOrFail($id);
        $nasabah->delete();
        return response()->json(['status' => 'success', 'message' => 'Nasabah dihapus']);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $import = new NasabahImport();
        Excel::import($import, $request->file('file'));

        $errors = $import->errors();

        return response()->json([
            'status'   => 'success',
            'message'  => "Berhasil mengimpor {$import->getImportedCount()} nasabah. {$import->getSkippedCount()} baris dilewati (username duplikat/kosong).",
            'imported' => $import->getImportedCount(),
            'skipped'  => $import->getSkippedCount(),
        ]);
    }

    public function downloadTemplate()
    {
        return Excel::download(new NasabahTemplateExport(), 'template_import_nasabah.xlsx');
    }
}
