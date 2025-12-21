<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\EksporController;
use App\Http\Controllers\HasilWawancaraController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\JadwalRekrutmenController;
use App\Http\Controllers\PengumumanController;
use Illuminate\Support\Facades\Route;

// Route Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route Terproteksi
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // ==========================================
    // Route FCM Token Management
    // ==========================================
    Route::post('/fcm/register', [FCMController::class, 'registerToken']);
    Route::post('/fcm/unregister', [FCMController::class, 'unregisterToken']);
    Route::get('/fcm/devices', [FCMController::class, 'getDevices']);
    Route::delete('/fcm/devices/{deviceId}', [FCMController::class, 'deleteDevice']);

    // ==========================================
    // Route Session Management (HYBRID APPROACH)
    // ==========================================
    Route::get('/session/check', [SessionController::class, 'checkSession']);
    Route::get('/session/list', [SessionController::class, 'getActiveSessions']);
    Route::delete('/session/{id}', [SessionController::class, 'revokeSession']);
    Route::post('/session/revoke-others', [SessionController::class, 'revokeOtherSessions']);

    // ==========================================
    // Route Hasil Wawancara (Modul Afiq - Fitur 16-17)
    // ==========================================
    Route::post('/wawancara/hasil', [HasilWawancaraController::class, 'simpan']);      // 16. Input hasil
    Route::put('/wawancara/hasil/{id}', [HasilWawancaraController::class, 'ubah']);    // 17. Ubah hasil
    Route::get('/wawancara/hasil', [HasilWawancaraController::class, 'daftar']);       // Lihat semua
    Route::get('/wawancara/hasil/{id}', [HasilWawancaraController::class, 'lihat']);   // Lihat by ID

    // ==========================================
    // Route Pengumuman (Modul Afiq - Fitur 18-19)
    // ==========================================
    Route::post('/pengumuman', [PengumumanController::class, 'simpan']);               // 18. Buat pengumuman
    Route::get('/pengumuman', [PengumumanController::class, 'daftar']);                // 19. Lihat pengumuman
    Route::get('/pengumuman/{id}', [PengumumanController::class, 'lihat']);            // Lihat by ID

    // ==========================================
    // Route Ekspor (Modul Afiq - Fitur 20)
    // ==========================================
    Route::get('/ekspor/hasil-seleksi', [EksporController::class, 'generate']);        // 20. Generate Excel
    Route::get('/ekspor/hasil-seleksi/unduh', [EksporController::class, 'unduh']);     // Unduh Excel

    // ==========================================
    // Route Divisi (Helper)
    // ==========================================
    Route::get('/divisi', [DivisiController::class, 'daftar']);

    // ==========================================
    // Route Peserta/Data Pendaftar (Modul 1 - Fitur 2-7)
    // ==========================================
    Route::get('/peserta', [PesertaController::class, 'index']);                    // 3. Lihat daftar
    Route::get('/peserta/{id}', [PesertaController::class, 'show']);                 // 5. Lihat detail
    Route::post('/peserta', [PesertaController::class, 'store']);                    // 2. Tambah data
    Route::put('/peserta/{id}', [PesertaController::class, 'update']);               // 6. Ubah data
    Route::delete('/peserta/{id}', [PesertaController::class, 'destroy']);          // 7. Hapus data
    Route::post('/peserta/import-excel', [PesertaController::class, 'importExcel']); // Import Excel
    Route::put('/peserta/{id}/status-seleksi-berkas', [PesertaController::class, 'updateStatusSeleksiBerkas']); // Update status seleksi berkas
    Route::get('/peserta/lulus-tanpa-jadwal', [PesertaController::class, 'getPesertaLulusTanpaJadwal']); // Get peserta lulus tanpa jadwal

    // ==========================================
    // Route Jadwal Rekrutmen
    // ==========================================
    Route::get('/jadwal-rekrutmen', [JadwalRekrutmenController::class, 'index']);      // Lihat semua
    Route::get('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'show']);  // Lihat detail
    Route::post('/jadwal-rekrutmen', [JadwalRekrutmenController::class, 'store']);      // Tambah jadwal
    Route::put('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'update']); // Ubah jadwal
    Route::delete('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'destroy']); // Hapus jadwal
    Route::post('/jadwal-rekrutmen/{id}/peserta', [JadwalRekrutmenController::class, 'assignPeserta']); // Assign peserta ke jadwal
    Route::get('/jadwal-rekrutmen/{id}/peserta', [JadwalRekrutmenController::class, 'getPeserta']); // Get peserta di jadwal
    Route::delete('/jadwal-rekrutmen/{id}/peserta/{pesertaId}', [JadwalRekrutmenController::class, 'removePeserta']); // Remove peserta dari jadwal
});
