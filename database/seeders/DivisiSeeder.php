<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $divisi = [
            ['nama' => 'Acara', 'koordinator' => 'Ihsan'],
            ['nama' => 'Humas', 'koordinator' => 'Anisa'],
            ['nama' => 'Konsumsi', 'koordinator' => 'Budi'],
            ['nama' => 'Perlengkapan', 'koordinator' => 'Siti'],
            ['nama' => 'Pubdok', 'koordinator' => 'Rina'],
            ['nama' => 'Keamanan', 'koordinator' => 'Andi'],
        ];

        foreach ($divisi as $d) {
            Divisi::create($d);
        }
    }
}
