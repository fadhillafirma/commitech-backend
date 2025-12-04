<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    /**
     * 18. Buat pengumuman akhir seleksi (Create)
     */
    public function simpan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'peserta_ids' => 'nullable|array',
            'peserta_ids.*' => 'exists:peserta,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $pengumuman = Pengumuman::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'tanggal_publikasi' => now(),
        ]);

        // Tambahkan peserta jika ada
        if ($request->has('peserta_ids') && is_array($request->peserta_ids)) {
            $pengumuman->peserta()->attach($request->peserta_ids);
        }

        $pengumuman->load('peserta.hasilWawancara');

        return response()->json([
            'sukses' => true,
            'pesan' => 'Pengumuman berhasil dibuat',
            'data' => $this->formatPengumuman($pengumuman)
        ], 201);
    }

    /**
     * 19. Lihat pengumuman akhir (Read)
     */
    public function daftar()
    {
        $pengumuman = Pengumuman::with('peserta.hasilWawancara')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data berhasil dimuat',
            'data' => $pengumuman->map(fn($p) => $this->formatPengumuman($p))
        ]);
    }

    /**
     * Lihat pengumuman by ID
     */
    public function lihat($id)
    {
        $pengumuman = Pengumuman::with('peserta.hasilWawancara')->find($id);

        if (!$pengumuman) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Pengumuman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data berhasil dimuat',
            'data' => $this->formatPengumuman($pengumuman)
        ]);
    }

    private function formatPengumuman($pengumuman)
    {
        return [
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
            'isi' => $pengumuman->isi,
            'tanggal_publikasi' => $pengumuman->tanggal_publikasi?->toISOString(),
            'peserta' => $pengumuman->peserta->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'nim' => $p->nim,
                'divisi' => $p->hasilWawancara->divisi ?? '-',
                'status' => $p->hasilWawancara->status ?? 'pending',
            ]),
            'dibuat_pada' => $pengumuman->created_at->toISOString(),
            'diubah_pada' => $pengumuman->updated_at->toISOString(),
        ];
    }
}
