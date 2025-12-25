<?php

namespace App\Http\Controllers;

use App\Models\HasilWawancara;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HasilWawancaraController extends Controller
{
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peserta_id' => 'required|exists:peserta,id',
            'status' => 'required|in:pending,diterima,ditolak',
            'divisi' => 'required_if:status,diterima|nullable|string',
            'alasan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $existing = HasilWawancara::where('peserta_id', $request->peserta_id)->first();
        if ($existing) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Hasil wawancara untuk peserta ini sudah ada. Gunakan endpoint ubah.'
            ], 400);
        }

        $hasil = HasilWawancara::create([
            'peserta_id' => $request->peserta_id,
            'status' => $request->status,
            'divisi' => $request->status === 'diterima' ? $request->divisi : null,
            'alasan' => $request->status === 'ditolak' ? $request->alasan : null,
            'waktu_wawancara' => now(),
        ]);

        Peserta::where('id', $request->peserta_id)->update([
            'status_wawancara' => $request->status
        ]);

        $hasil->load('peserta');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Hasil wawancara berhasil disimpan',
            'data' => $this->formatHasil($hasil)
        ], 201);
    }

    public function ubah(Request $request, $id)
    {
        $hasil = HasilWawancara::find($id);

        if (!$hasil) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,diterima,ditolak',
            'divisi' => 'required_if:status,diterima|nullable|string',
            'alasan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $hasil->update([
            'status' => $request->status,
            'divisi' => $request->status === 'diterima' ? $request->divisi : null,
            'alasan' => $request->status === 'ditolak' ? $request->alasan : null,
        ]);

        Peserta::where('id', $hasil->peserta_id)->update([
            'status_wawancara' => $request->status
        ]);

        $hasil->load('peserta');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Hasil wawancara berhasil diubah',
            'data' => $this->formatHasil($hasil)
        ]);
    }

    public function daftar()
    {
        $hasil = HasilWawancara::with('peserta')->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data berhasil dimuat',
            'data' => $hasil->map(fn($h) => $this->formatHasil($h))
        ]);
    }

    public function lihat($id)
    {
        $hasil = HasilWawancara::with('peserta')->find($id);

        if (!$hasil) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data berhasil dimuat',
            'data' => $this->formatHasil($hasil)
        ]);
    }

    private function formatHasil($hasil)
    {
        return [
            'id' => $hasil->id,
            'peserta_id' => $hasil->peserta_id,
            'nama_peserta' => $hasil->peserta->nama ?? '-',
            'tanggal_jadwal' => $hasil->peserta->jadwalRekrutmen?->tanggal_mulai ?? '-',
            'waktu_jadwal' => $hasil->peserta->jadwalRekrutmen?->waktu_mulai ?? '-',
            'lokasi' => $hasil->peserta->jadwalRekrutmen?->lokasi ?? '-',
            'status' => $hasil->status,
            'divisi' => $hasil->divisi,
            'alasan' => $hasil->alasan,
            'waktu_wawancara' => $hasil->waktu_wawancara?->toISOString(),
            'dibuat_pada' => $hasil->created_at->toISOString(),
            'diubah_pada' => $hasil->updated_at->toISOString(),
        ];
    }
}
