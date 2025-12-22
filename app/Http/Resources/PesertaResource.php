<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesertaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nim' => $this->nim,
            'email' => $this->email,
            'telepon' => $this->telepon,
            'jurusan' => $this->jurusan,
            'angkatan' => $this->angkatan,
            'pilihan_divisi_1' => $this->pilihan_divisi_1,
            'pilihan_divisi_2' => $this->pilihan_divisi_2,
            'pilihan_divisi_3' => $this->pilihan_divisi_3,
            'alasan_1' => $this->alasan_1,
            'alasan_2' => $this->alasan_2,
            'alasan_3' => $this->alasan_3,
            'krs_terakhir' => $this->krs_terakhir,
            'formulir_pendaftaran' =>$this->formulir_pendaftaran,
            'surat_komitmen' =>$this->surat_komitmen,
            
            // ✅ Guarantee boolean type - fix boolean issue!
            'pindah_divisi' => (bool) $this->pindah_divisi,
            
            // Jadwal wawancara menggunakan jadwal_rekrutmen_id
            'jadwal_rekrutmen_id' => $this->jadwal_rekrutmen_id,
            'tanggal_jadwal' => $this->jadwalRekrutmen?->tanggal_mulai,
            'waktu_jadwal' => $this->jadwalRekrutmen?->waktu_mulai,
            'lokasi' => $this->lokasi,
            'status_seleksi_berkas' => $this->status_seleksi_berkas ?? 'belum_direview',
            'status_wawancara' => $this->status_wawancara ?? 'pending',
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

