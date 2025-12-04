<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilWawancara extends Model
{
    use HasFactory;

    protected $table = 'hasil_wawancara';

    protected $fillable = [
        'peserta_id',
        'status',
        'divisi',
        'alasan',
        'waktu_wawancara',
    ];

    protected $casts = [
        'waktu_wawancara' => 'datetime',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
}
