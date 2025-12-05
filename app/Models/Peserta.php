<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';

    protected $fillable = [
        'email',
        'nim',
        'nama',
        'telepon',
        'jurusan',
        'angkatan',
        'pilihan_divisi_1',
        'pilihan_divisi_2',
        'pilihan_divisi_3',
        'alasan_1',
        'alasan_2',
        'alasan_3',
        'krs_terakhir',
        'formulir_pendaftaran',
        'surat_komitmen',
        'pindah_divisi',
        'tanggal_jadwal',
        'waktu_jadwal',
        'lokasi',
    ];

    /**
     * Cast fields
     * 
     * PERBAIKAN: formulir_pendaftaran dan surat_komitmen sekarang string (URL)
     * bukan boolean, agar bisa menyimpan link Google Drive
     */
    protected $casts = [
        'pindah_divisi' => 'boolean',
    ];

    public function hasilWawancara()
    {
        return $this->hasOne(HasilWawancara::class, 'peserta_id');
    }

    public function pengumuman()
    {
        return $this->belongsToMany(Pengumuman::class, 'pengumuman_peserta')
            ->withTimestamps();
    }
}
