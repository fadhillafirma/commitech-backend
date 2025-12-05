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
            $skipped = 0;
            
            // DEBUG: Log jumlah rows
            \Log::info("=== IMPORT EXCEL START ===");
            \Log::info("Total rows in Excel (including header): " . count($rows));

            foreach ($rows as $index => $row) {
                // Skip header
                if ($index === 0) {
                    \Log::info("Row 0: HEADER - skipped");
                    continue;
                }

                // DEBUG: Log row untuk debugging (hanya 3 kolom pertama untuk privacy)
                \Log::info("Row {$index}: Email=" . ($row[1] ?? 'null') . ", Nama=" . ($row[2] ?? 'null') . ", NIM=" . ($row[3] ?? 'null'));
                
                // PERBAIKAN: Cek jika row benar-benar kosong (semua kolom kosong)
                $isRowEmpty = true;
                foreach ($row as $cell) {
                    if (!empty($cell) && trim($cell) !== '') {
                        $isRowEmpty = false;
                        break;
                    }
                }
                
                if ($isRowEmpty) {
                    \Log::info("Row {$index}: COMPLETELY EMPTY - skipped");
                    $skipped++;
                    continue;
                }
                
                // Ambil nama dan bersihkan whitespace
                $nama = isset($row[2]) ? trim((string)$row[2]) : '';
                
                // Skip jika nama kosong
                if ($nama === '' || $nama === null) {
                    \Log::warning("Row {$index}: Nama kosong atau null - skipped");
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => 'Nama lengkap kosong (kolom C). Data: ' . json_encode(array_slice($row, 0, 4))
                    ];
                    $skipped++;
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
                    // 12. KRS Terbaru (Link Google Drive)
                    // 13. Formulir Pendaftaran (Link Google Drive)
                    // 14. Surat Komitmen (Link Google Drive)
                    // 15. Pindah divisi
                    
                    // PERBAIKAN: Helper function untuk clean dan convert value
                    $cleanValue = function($value) {
                        if ($value === null || $value === '') return null;
                        $cleaned = trim((string)$value);
                        return $cleaned === '' ? null : $cleaned;
                    };
                    
                    // Prepare data dengan cleaning
                    $data = [
                        'nama' => $nama,
                        'email' => $cleanValue($row[1] ?? null),
                        'nim' => $cleanValue($row[3] ?? null),
                        'jurusan' => $cleanValue($row[4] ?? null),
                        'angkatan' => $cleanValue($row[5] ?? null),
                        'pilihan_divisi_1' => $cleanValue($row[6] ?? null),
                        'alasan_1' => $cleanValue($row[7] ?? null),
                        'pilihan_divisi_2' => $cleanValue($row[8] ?? null),
                        'alasan_2' => $cleanValue($row[9] ?? null),
                        'pilihan_divisi_3' => $cleanValue($row[10] ?? null),
                        'alasan_3' => $cleanValue($row[11] ?? null),
                        'krs_terakhir' => $cleanValue($row[12] ?? null),
                        'formulir_pendaftaran' => $cleanValue($row[13] ?? null),
                        'surat_komitmen' => $cleanValue($row[14] ?? null),
                        'pindah_divisi' => false, // Default false
                    ];
                    
                    // Handle pindah_divisi boolean
                    if (isset($row[15]) && !empty($row[15])) {
                        $pindahDivisi = strtolower(trim((string)$row[15]));
                        $data['pindah_divisi'] = in_array($pindahDivisi, ['ya', 'yes', '1', 'true']);
                    }
                    
                    \Log::info("Row {$index}: Creating peserta dengan nama '{$nama}'");
                    
                    // Create peserta
                    Peserta::create($data);

                    $imported++;
                    \Log::info("Row {$index}: SUCCESS - {$nama} imported");
                } catch (\Exception $e) {
                    \Log::error("Row {$index}: FAILED - " . $e->getMessage());
                    \Log::error("Row {$index}: Stack trace - " . $e->getTraceAsString());
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'nama' => $nama ?? 'Unknown'
                    ];
                }
            }
            
            \Log::info("=== IMPORT EXCEL END ===");
            \Log::info("Total imported: {$imported}");
            \Log::info("Total errors: " . count($errors));
            \Log::info("Total skipped: {$skipped}");

            // Kembalikan response dengan detail errors
            $message = "Berhasil mengimpor {$imported} data peserta";
            if ($skipped > 0) {
                $message .= ". {$skipped} baris kosong dilewati";
            }
            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " baris gagal diimpor";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'total_rows' => count($rows) - 1, // Minus header
                    'errors' => count($errors) > 0 ? $errors : null
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

