<?php

namespace App\Http\Controllers;

use App\Models\JadwalRekrutmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JadwalRekrutmenController extends Controller
{
    /**
     * Daftar semua jadwal rekrutmen
     */
    public function index()
    {
        $jadwal = JadwalRekrutmen::orderBy('tanggal_mulai', 'asc')->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data jadwal rekrutmen berhasil diambil',
            'data' => $jadwal
        ], 200);
    }

    /**
     * Simpan jadwal rekrutmen baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'pewawancara' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $jadwal = JadwalRekrutmen::create([
            'judul' => $request->judul,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'pewawancara' => $request->pewawancara ?? '-',
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Jadwal rekrutmen berhasil disimpan',
            'data' => $jadwal
        ], 201);
    }

    /**
     * Lihat detail jadwal rekrutmen
     */
    public function show($id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data jadwal rekrutmen berhasil diambil',
            'data' => $jadwal
        ], 200);
    }

    /**
     * Ubah jadwal rekrutmen
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'pewawancara' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $jadwal->update([
            'judul' => $request->judul,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'pewawancara' => $request->pewawancara ?? '-',
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Jadwal rekrutmen berhasil diubah',
            'data' => $jadwal
        ], 200);
    }

    /**
     * Hapus jadwal rekrutmen
     */
    public function destroy($id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        $jadwal->delete();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Jadwal rekrutmen berhasil dihapus'
        ], 200);
    }
}

