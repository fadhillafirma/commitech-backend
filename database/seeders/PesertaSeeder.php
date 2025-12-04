<?php

namespace Database\Seeders;

use App\Models\Peserta;
use Illuminate\Database\Seeder;

class PesertaSeeder extends Seeder
{
    public function run(): void
    {
        $peserta = [
            [
                'nama' => 'Fadhilla Firma',
                'nim' => '2311522031',
                'email' => 'fadhilla@mail.com',
                'telepon' => '081234567890',
                'pilihan_divisi_1' => 'Konsumsi',
                'pilihan_divisi_2' => 'Acara',
                'alasan_1' => 'Suka masak',
                'alasan_2' => 'Suka keramaian',
                'tanggal_jadwal' => '15 Okt 2025',
                'waktu_jadwal' => '07.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
            [
                'nama' => 'Afiq Congkel',
                'nim' => '2311523011',
                'email' => 'afiq@mail.com',
                'telepon' => '081234567891',
                'pilihan_divisi_1' => 'Humas',
                'pilihan_divisi_2' => 'Pubdok',
                'alasan_1' => 'Suka komunikasi',
                'alasan_2' => 'Suka foto',
                'tanggal_jadwal' => '15 Okt 2025',
                'waktu_jadwal' => '08.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
            [
                'nama' => 'Farhan Firki',
                'nim' => '2311522037',
                'email' => 'farhan@mail.com',
                'telepon' => '081234567892',
                'pilihan_divisi_1' => 'Perlengkapan',
                'pilihan_divisi_2' => 'Keamanan',
                'alasan_1' => 'Suka angkat-angkat',
                'alasan_2' => 'Suka ketertiban',
                'tanggal_jadwal' => '15 Okt 2025',
                'waktu_jadwal' => '09.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
            [
                'nama' => 'Diaz Jelek Hitam',
                'nim' => '2311521015',
                'email' => 'diaz@mail.com',
                'telepon' => '081234567893',
                'pilihan_divisi_1' => 'Acara',
                'pilihan_divisi_2' => 'Konsumsi',
                'alasan_1' => 'Suka buat event',
                'alasan_2' => 'Suka ngemil',
                'tanggal_jadwal' => '15 Okt 2025',
                'waktu_jadwal' => '10.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
            [
                'nama' => 'Nadya Putri',
                'nim' => '2311522045',
                'email' => 'nadya@mail.com',
                'telepon' => '081234567894',
                'pilihan_divisi_1' => 'Humas',
                'pilihan_divisi_2' => 'Acara',
                'alasan_1' => 'Suka public speaking',
                'alasan_2' => 'Suka koordinasi',
                'tanggal_jadwal' => '17 Okt 2025',
                'waktu_jadwal' => '07.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
            [
                'nama' => 'Dimas Aditya',
                'nim' => '2311522050',
                'email' => 'dimas@mail.com',
                'telepon' => '081234567895',
                'pilihan_divisi_1' => 'Pubdok',
                'pilihan_divisi_2' => 'Humas',
                'alasan_1' => 'Suka desain',
                'alasan_2' => 'Suka menulis',
                'tanggal_jadwal' => '17 Okt 2025',
                'waktu_jadwal' => '08.00 WIB',
                'lokasi' => 'Sekretariat BEM KM FTI',
            ],
        ];

        foreach ($peserta as $p) {
            Peserta::create($p);
        }
    }
}
