<?php

namespace App\Http\Controllers;

use App\Http\Resources\PesertaResource;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PesertaController extends Controller
{
    /**
     * Get all peserta (data pendaftar)
     */
    public function index(Request $request)
    {
        try {
            $query = Peserta::query();

            // Optional: Add search/filter functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Pagination: 20 data per page
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            // Validasi per_page maksimal 100
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

    /**
     * Get single peserta by ID
     */
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

    /**
     * Create new peserta
     */
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
                'tanggal_jadwal' => 'nullable|string',
                'waktu_jadwal' => 'nullable|string',
                'lokasi' => 'nullable|string|max:255',
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

    /**
     * Update peserta
     */
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
                'tanggal_jadwal' => 'nullable|string',
                'waktu_jadwal' => 'nullable|string',
                'lokasi' => 'nullable|string|max:255',
            ]);

            $peserta->update($validated);
            $peserta->refresh(); // Refresh untuk mendapatkan data terbaru

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

    /**
     * Delete peserta
     */
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

            // Reset auto-increment ID setelah delete
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

    /**
     * Reset auto-increment ID ke 1 setelah delete
     * Jika tabel kosong, ID akan kembali mulai dari 1
     * Jika masih ada data, reset ke max ID + 1 untuk menghindari konflik
     */
    private function resetAutoIncrement()
    {
        try {
            $dbDriver = DB::connection()->getDriverName();
            
            // Cek apakah tabel kosong
            $count = Peserta::count();
            
            if ($count == 0) {
                // Jika tabel kosong setelah delete, reset auto-increment ke 1
                // ID berikutnya akan mulai dari 1
                if ($dbDriver === 'mysql') {
                    DB::statement('ALTER TABLE peserta AUTO_INCREMENT = 1');
                } elseif ($dbDriver === 'sqlite') {
                    // SQLite: Update sqlite_sequence untuk reset ke 1
                    DB::statement("UPDATE sqlite_sequence SET seq = 0 WHERE name = 'peserta'");
                }
            } else {
                // Jika masih ada data, reset ke next available ID (max ID + 1)
                // Ini menghindari gap ID yang terlalu besar
                $maxId = Peserta::max('id');
                $nextId = ($maxId ?? 0) + 1;
                
                if ($dbDriver === 'mysql') {
                    DB::statement("ALTER TABLE peserta AUTO_INCREMENT = {$nextId}");
                } elseif ($dbDriver === 'sqlite') {
                    // SQLite: Update sqlite_sequence ke max ID yang ada
                    DB::statement("UPDATE sqlite_sequence SET seq = {$maxId} WHERE name = 'peserta'");
                }
            }
        } catch (\Exception $e) {
            // Silent fail - reset auto-increment bukan critical operation
            // Error bisa diabaikan karena tidak critical
        }
    }

    /**
     * Import peserta from Excel file
     */
    public function importExcel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
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

            // Skip header row (row 1)
            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                // Skip header
                if ($index === 0) {
                    continue;
                }

                // Skip empty rows - cek nama lengkap (index 2) yang wajib
                if (empty($row[2]) || trim($row[2]) === '') {
                    continue;
                }

                try {
                    // Format Excel: 
                    // 0. Timestamp (DI-SKIP - tidak digunakan)
                    // 1. Email
                    // 2. Nama Lengkap
                    // 3. NIM
                    // 4. Jurusan
                    // 5. Angkatan
                    // 6. Pilihan 1
                    // 7. Alasan Memilih (Pilihan 1)
                    // 8. Pilihan 2
                    // 9. Alasan Memilih (Pilihan 2)
                    // 10. Pilihan 3
                    // 11. Alasan Memilih (Pilihan 3)
                    // 12. KRS Terakhir
                    // 13. Formulir Pendaftaran
                    // 14. Surat Komitmen
                    // 15. Pindah divisi
                    
                    // NOTE: Timestamp di index 0 diabaikan/tidak digunakan
                    
                    // Skip jika nama kosong
                    $nama = trim($row[2] ?? ''); // Nama Lengkap di index 2 (Timestamp di index 0 diabaikan)
                    if (empty($nama)) {
                        continue;
                    }
                    
                    Peserta::create([
                        'nama' => $nama,
                        'email' => !empty($row[1]) ? trim($row[1]) : null, // Email di index 1
                        'nim' => !empty($row[3]) ? trim($row[3]) : null, // NIM di index 3
                        'jurusan' => !empty($row[4]) ? trim($row[4]) : null, // Jurusan di index 4
                        'angkatan' => !empty($row[5]) ? trim($row[5]) : null, // Angkatan di index 5
                        'pilihan_divisi_1' => !empty($row[6]) ? trim($row[6]) : null, // Pilihan 1 di index 6
                        'alasan_1' => !empty($row[7]) ? trim($row[7]) : null, // Alasan Memilih di index 7
                        'pilihan_divisi_2' => !empty($row[8]) ? trim($row[8]) : null, // Pilihan 2 di index 8
                        'alasan_2' => !empty($row[9]) ? trim($row[9]) : null, // Alasan Memilih di index 9
                        'pilihan_divisi_3' => !empty($row[10]) ? trim($row[10]) : null, // Pilihan 3 di index 10
                        'alasan_3' => !empty($row[11]) ? trim($row[11]) : null, // Alasan Memilih di index 11
                        'krs_terakhir' => !empty($row[12]) ? trim($row[12]) : null, // KRS Terakhir di index 12
                        'formulir_pendaftaran' => !empty($row[13]) && (strtolower(trim($row[13])) === 'ya' || strtolower(trim($row[13])) === 'yes' || trim($row[13]) === '1'),
                        'surat_komitmen' => !empty($row[14]) && (strtolower(trim($row[14])) === 'ya' || strtolower(trim($row[14])) === 'yes' || trim($row[14]) === '1'),
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
}

