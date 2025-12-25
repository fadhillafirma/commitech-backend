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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/fcm/register', [FCMController::class, 'registerToken']);
    Route::post('/fcm/unregister', [FCMController::class, 'unregisterToken']);
    Route::get('/fcm/devices', [FCMController::class, 'getDevices']);
    Route::delete('/fcm/devices/{deviceId}', [FCMController::class, 'deleteDevice']);

    Route::get('/session/check', [SessionController::class, 'checkSession']);
    Route::get('/session/list', [SessionController::class, 'getActiveSessions']);
    Route::delete('/session/{id}', [SessionController::class, 'revokeSession']);
    Route::post('/session/revoke-others', [SessionController::class, 'revokeOtherSessions']);

    Route::post('/wawancara/hasil', [HasilWawancaraController::class, 'simpan']);
    Route::put('/wawancara/hasil/{id}', [HasilWawancaraController::class, 'ubah']);
    Route::get('/wawancara/hasil', [HasilWawancaraController::class, 'daftar']);
    Route::get('/wawancara/hasil/{id}', [HasilWawancaraController::class, 'lihat']);

    Route::post('/pengumuman', [PengumumanController::class, 'simpan']);
    Route::get('/pengumuman', [PengumumanController::class, 'daftar']);
    Route::get('/pengumuman/{id}', [PengumumanController::class, 'lihat']);

    Route::get('/ekspor/hasil-seleksi', [EksporController::class, 'generate']);
    Route::get('/ekspor/hasil-seleksi/unduh', [EksporController::class, 'unduh']);

    Route::get('/divisi', [DivisiController::class, 'daftar']);

    Route::get('/peserta', [PesertaController::class, 'index']);
    Route::get('/peserta/lulus-tanpa-jadwal', [PesertaController::class, 'getPesertaLulusTanpaJadwal']);
    Route::get('/peserta/count-lulus', [PesertaController::class, 'countPesertaLulus']);
    Route::get('/peserta/pending-wawancara', [PesertaController::class, 'getPesertaPendingWawancara']);
    Route::get('/peserta/{id}', [PesertaController::class, 'show']);
    Route::post('/peserta', [PesertaController::class, 'store']);
    Route::put('/peserta/{id}', [PesertaController::class, 'update']);
    Route::delete('/peserta/{id}', [PesertaController::class, 'destroy']);
    Route::post('/peserta/import-excel', [PesertaController::class, 'importExcel']);
    Route::put('/peserta/{id}/status-seleksi-berkas', [PesertaController::class, 'updateStatusSeleksiBerkas']);

    Route::get('/jadwal-rekrutmen', [JadwalRekrutmenController::class, 'index']);
    Route::get('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'show']);
    Route::post('/jadwal-rekrutmen', [JadwalRekrutmenController::class, 'store']);
    Route::put('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'update']);
    Route::delete('/jadwal-rekrutmen/{id}', [JadwalRekrutmenController::class, 'destroy']);
    Route::post('/jadwal-rekrutmen/{id}/peserta', [JadwalRekrutmenController::class, 'assignPeserta']);
    Route::get('/jadwal-rekrutmen/{id}/peserta', [JadwalRekrutmenController::class, 'getPeserta']);
    Route::delete('/jadwal-rekrutmen/{id}/peserta/{pesertaId}', [JadwalRekrutmenController::class, 'removePeserta']);
});
