<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    protected $fillable = [
        'nama',
        'koordinator',
    ];

    public function hasilWawancara()
    {
        return $this->hasMany(HasilWawancara::class, 'divisi', 'nama');
    }
}
