<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DivisiController extends Controller
{
    public function daftar()
    {
        $divisi = Divisi::all();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data berhasil dimuat',
            'data' => $divisi->map(fn($d) => [
                'id' => $d->id,
                'nama' => $d->nama,
                'koordinator' => $d->koordinator,
            ])
        ]);
    }

    public function tambah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:divisi,nama',
            'koordinator' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $divisi = Divisi::create([
            'nama' => $request->nama,
            'koordinator' => $request->koordinator,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Divisi berhasil ditambahkan',
            'data' => [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'koordinator' => $divisi->koordinator,
            ]
        ], 201);
    }

    public function ubah(Request $request, $id)
    {
        $divisi = Divisi::find($id);

        if (!$divisi) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Divisi tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255|unique:divisi,nama,' . $id,
            'koordinator' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $divisi->update([
            'nama' => $request->nama,
            'koordinator' => $request->koordinator,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Divisi berhasil diubah',
            'data' => [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'koordinator' => $divisi->koordinator,
            ]
        ]);
    }

    public function hapus($id)
    {
        $divisi = Divisi::find($id);

        if (!$divisi) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Divisi tidak ditemukan'
            ], 404);
        }

        $divisi->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Divisi berhasil dihapus'
        ]);
    }
}
