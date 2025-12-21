<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalRekrutmen extends Model
{
    use HasFactory;

    protected $table = 'jadwal_rekrutmen';

    protected $fillable = [
        'judul',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'pewawancara',
    ];

    public function peserta()
    {
        return $this->hasMany(Peserta::class, 'jadwal_rekrutmen_id');
    }
}

