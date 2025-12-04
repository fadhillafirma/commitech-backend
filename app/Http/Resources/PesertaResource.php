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
            
            // ✅ Guarantee boolean type - fix boolean issue!
            'formulir_pendaftaran' => (bool) $this->formulir_pendaftaran,
            'surat_komitmen' => (bool) $this->surat_komitmen,
            'pindah_divisi' => (bool) $this->pindah_divisi,
            
            'tanggal_jadwal' => $this->tanggal_jadwal,
            'waktu_jadwal' => $this->waktu_jadwal,
            'lokasi' => $this->lokasi,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

