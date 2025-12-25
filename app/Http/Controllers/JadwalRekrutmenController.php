<?php

namespace App\Http\Controllers;

use App\Models\JadwalRekrutmen;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JadwalRekrutmenController extends Controller
{
    public function index()
    {
        $jadwal = JadwalRekrutmen::orderBy('tanggal_mulai', 'asc')->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data jadwal rekrutmen berhasil diambil',
            'data' => $jadwal
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|string',
            'tanggal_selesai' => 'required|string',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'pewawancara' => 'nullable|string|max:255',
            'lokasi' => 'nullable|string|max:255',
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
            'lokasi' => $request->lokasi,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Jadwal rekrutmen berhasil disimpan',
            'data' => $jadwal
        ], 201);
    }

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
            'lokasi' => 'nullable|string|max:255',
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
            'lokasi' => $request->lokasi,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Jadwal rekrutmen berhasil diubah',
            'data' => $jadwal
        ], 200);
    }

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

            $tanggalJadwal = $jadwal->tanggal_mulai;
            $waktuMulai = $jadwal->waktu_mulai;

            $pesertaIds = $request->peserta_ids;
            $updatedCount = 0;
            $waktuPerPeserta = [];

            $waktuMulaiParts = explode(':', $waktuMulai);
            $jamMulai = (int)($waktuMulaiParts[0] ?? 0);
            $menitMulai = (int)($waktuMulaiParts[1] ?? 0);

            foreach ($pesertaIds as $pesertaId) {
                $peserta = Peserta::find($pesertaId);
                if ($peserta) {
                    $peserta->update([
                        'jadwal_rekrutmen_id' => $jadwal->id,
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

    public function getPeserta($id)
    {
        $jadwal = JadwalRekrutmen::find($id);

        if (!$jadwal) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Jadwal rekrutmen tidak ditemukan'
            ], 404);
        }

        $peserta = Peserta::where('jadwal_rekrutmen_id', $jadwal->id)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'sukses' => true,
            'pesan' => 'Data peserta berhasil diambil',
            'data' => $peserta
        ], 200);
    }

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

        $peserta->update([
            'jadwal_rekrutmen_id' => null,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan' => 'Peserta berhasil dihapus dari jadwal'
        ], 200);
    }
}

