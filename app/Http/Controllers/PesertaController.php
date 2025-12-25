<?php

namespace App\Http\Controllers;

use App\Http\Resources\PesertaResource;
use App\Models\Peserta;
use App\Models\JadwalRekrutmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaLulusExport;
use App\Imports\PesertaImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Peserta::query();

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            $perPage = min($perPage, 100);
            
            $peserta = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Data peserta berhasil diambil',
                'data' => PesertaResource::collection($peserta->items()),
                'pagination' => [
                    'current_page' => $peserta->currentPage(),
                    'last_page' => $peserta->lastPage(),
                    'per_page' => $peserta->perPage(),
                    'total' => $peserta->total(),
                    'from' => $peserta->firstItem(),
                    'to' => $peserta->lastItem(),
                    'has_more' => $peserta->hasMorePages()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPesertaPendingWawancara(Request $request)
    {
        try {
            $peserta = Peserta::query()
                ->where('status_seleksi_berkas', 'lulus')
                ->whereDoesntHave('hasilWawancara')
                ->orderBy('nama', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data peserta pending wawancara berhasil diambil',
                'data' => PesertaResource::collection($peserta)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peserta pending wawancara',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function countPesertaLulus(Request $request)
    {
        try {
            $count = Peserta::query()
                ->where('status_seleksi_berkas', 'lulus')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Jumlah peserta lulus seleksi berkas berhasil diambil',
                'data' => [
                    'count' => $count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah peserta lulus seleksi berkas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peserta tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data peserta berhasil diambil',
                'data' => new PesertaResource($peserta)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'nim' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'telepon' => 'nullable|string|max:20',
                'pilihan_divisi_1' => 'nullable|string|max:255',
                'pilihan_divisi_2' => 'nullable|string|max:255',
                'alasan_1' => 'nullable|string',
                'alasan_2' => 'nullable|string',
                'jadwal_rekrutmen_id' => 'nullable|integer|exists:jadwal_rekrutmen,id',
            ]);

            $peserta = Peserta::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil ditambahkan',
                'data' => new PesertaResource($peserta)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peserta tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'nama' => 'sometimes|required|string|max:255',
                'nim' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'telepon' => 'nullable|string|max:20',
                'pilihan_divisi_1' => 'nullable|string|max:255',
                'pilihan_divisi_2' => 'nullable|string|max:255',
                'alasan_1' => 'nullable|string',
                'alasan_2' => 'nullable|string',
                'jadwal_rekrutmen_id' => 'nullable|integer|exists:jadwal_rekrutmen,id',
            ]);

            $peserta->update($validated);
            $peserta->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil diupdate',
                'data' => new PesertaResource($peserta)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peserta tidak ditemukan'
                ], 404);
            }

            $peserta->delete();

            $this->resetAutoIncrement();

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function resetAutoIncrement()
    {
        try {
            $dbDriver = DB::connection()->getDriverName();
            
            $count = Peserta::count();
            
            if ($count == 0) {
                if ($dbDriver === 'mysql') {
                    DB::statement('ALTER TABLE peserta AUTO_INCREMENT = 1');
                } elseif ($dbDriver === 'sqlite') {
                    DB::statement("UPDATE sqlite_sequence SET seq = 0 WHERE name = 'peserta'");
                }
            } else {
                $maxId = Peserta::max('id');
                $nextId = ($maxId ?? 0) + 1;
                
                if ($dbDriver === 'mysql') {
                    DB::statement("ALTER TABLE peserta AUTO_INCREMENT = {$nextId}");
                } elseif ($dbDriver === 'sqlite') {
                    DB::statement("UPDATE sqlite_sequence SET seq = {$maxId} WHERE name = 'peserta'");
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function importExcel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                if (empty($row[2]) || trim($row[2]) === '') {
                    continue;
                }

                try {
                    $nama = trim($row[2] ?? '');
                    if (empty($nama)) {
                        continue;
                    }
                    
                    Peserta::create([
                        'nama' => $nama,
                        'email' => !empty($row[1]) ? trim($row[1]) : null, 
                        'nim' => !empty($row[3]) ? trim($row[3]) : null, 
                        'jurusan' => !empty($row[4]) ? trim($row[4]) : null,
                        'angkatan' => !empty($row[5]) ? trim($row[5]) : null,
                        'pilihan_divisi_1' => !empty($row[6]) ? trim($row[6]) : null, 
                        'alasan_1' => !empty($row[7]) ? trim($row[7]) : null,
                        'pilihan_divisi_2' => !empty($row[8]) ? trim($row[8]) : null,
                        'alasan_2' => !empty($row[9]) ? trim($row[9]) : null,
                        'pilihan_divisi_3' => !empty($row[10]) ? trim($row[10]) : null,
                        'alasan_3' => !empty($row[11]) ? trim($row[11]) : null,
                        'krs_terakhir' => !empty($row[12]) ? trim($row[12]) : null,
                        'formulir_pendaftaran' => !empty($row[13]) ? trim($row[13]) : null,
                        'surat_komitmen' => !empty($row[14]) ? trim($row[14]) : null,
                        'pindah_divisi' => !empty($row[15]) && (strtolower(trim($row[15])) === 'ya' || strtolower(trim($row[15])) === 'yes' || trim($row[15]) === '1'),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$imported} data peserta",
                'data' => [
                    'imported' => $imported,
                    'errors' => $errors
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor file Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPesertaLulusTanpaJadwal(Request $request)
    {
        try {
            $query = Peserta::query()
                ->where('status_seleksi_berkas', 'lulus')
                ->whereNull('jadwal_rekrutmen_id')
                ->orderBy('nama', 'asc');

            $peserta = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data peserta lulus tanpa jadwal berhasil diambil',
                'data' => PesertaResource::collection($peserta)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data peserta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatusSeleksiBerkas(Request $request, $id)
    {
        try {
            $peserta = Peserta::find($id);

            if (!$peserta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peserta tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'status' => 'required|string|in:lulus,tidak_lulus,belum_direview',
            ]);

            $peserta->update([
                'status_seleksi_berkas' => $validated['status']
            ]);

            $peserta->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Status seleksi berkas berhasil diupdate',
                'data' => new PesertaResource($peserta)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status seleksi berkas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

