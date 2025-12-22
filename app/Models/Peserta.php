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
        'jadwal_rekrutmen_id',
        'lokasi',
        'status_seleksi_berkas',
        'status_wawancara',
    ];

    /**
     * Cast boolean fields to ensure JSON returns true/false instead of 0/1
     */
    protected $casts = [
        'formulir_pendaftaran' => 'boolean',
        'surat_komitmen' => 'boolean',
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

    public function jadwalRekrutmen()
    {
        return $this->belongsTo(JadwalRekrutmen::class, 'jadwal_rekrutmen_id');
    }
}
