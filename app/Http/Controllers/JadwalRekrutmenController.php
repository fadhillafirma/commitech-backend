<?php

namespace App\Http\Controllers;

use App\Models\JadwalRekrutmen;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    /**
     * Assign peserta ke jadwal rekrutmen
     * 
     * Endpoint: POST /api/jadwal-rekrutmen/{id}/peserta
     * Body: { "peserta_ids": [1, 2, 3] }
     */
    public function assignPeserta(Request $request, $id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'peserta_ids' => 'required|array|min:1|max:5',
            'peserta_ids.*' => 'required|integer|exists:peserta,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Format tanggal dan waktu dari jadwal
            $tanggalJadwal = $jadwal->tanggal_mulai;
            $waktuMulai = $jadwal->waktu_mulai;
            $lokasi = "Sekretariat BEM KM FTI"; // Default lokasi atau bisa dari request

            // Update peserta dengan jadwal
            $pesertaIds = $request->peserta_ids;
            $updatedCount = 0;
            $waktuPerPeserta = []; // Untuk distribusi waktu per peserta

            // Hitung interval waktu per peserta (asumsi durasi per peserta 6 menit)
            $waktuMulaiParts = explode(':', $waktuMulai);
            $jamMulai = (int)($waktuMulaiParts[0] ?? 0);
            $menitMulai = (int)($waktuMulaiParts[1] ?? 0);

            foreach ($pesertaIds as $index => $pesertaId) {
                // Hitung waktu untuk peserta ini (setiap 6 menit)
                $menitJadwal = $menitMulai + ($index * 6);
                $jamJadwal = $jamMulai + (int)($menitJadwal / 60);
                $menitJadwal = $menitJadwal % 60;
                $waktuJadwal = sprintf("%02d:%02d", $jamJadwal, $menitJadwal) . " WIB";

                $peserta = Peserta::find($pesertaId);
                if ($peserta) {
                    $peserta->update([
                        'tanggal_jadwal' => $tanggalJadwal,
                        'waktu_jadwal' => $waktuJadwal,
                        'lokasi' => $lokasi,
                    ]);
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => "$updatedCount peserta berhasil di-assign ke jadwal",
                'data' => [
                    'jadwal_id' => $jadwal->id,
                    'peserta_count' => $updatedCount,
                    'peserta_ids' => $pesertaIds
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal assign peserta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get peserta yang di-assign ke jadwal rekrutmen
     * 
     * Endpoint: GET /api/jadwal-rekrutmen/{id}/peserta
     */
    public function getPeserta($id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        // Cari peserta yang memiliki tanggal_jadwal sama dengan jadwal ini
        $peserta = Peserta::where('tanggal_jadwal', $jadwal->tanggal_mulai)
            ->orderBy('waktu_jadwal', 'asc')
            ->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data peserta berhasil diambil',
            'data' => $peserta
        ], 200);
    }

    /**
     * Remove peserta dari jadwal rekrutmen
     * 
     * Endpoint: DELETE /api/jadwal-rekrutmen/{id}/peserta/{pesertaId}
     */
    public function removePeserta($id, $pesertaId)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        $peserta = Peserta::find($pesertaId);

        if (!$peserta) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Peserta tidak ditemukan'
            ], 404);
        }

        // Hapus jadwal dari peserta (set null)
        $peserta->update([
            'tanggal_jadwal' => null,
            'waktu_jadwal' => null,
            'lokasi' => null,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Peserta berhasil dihapus dari jadwal'
        ], 200);
    }
}

