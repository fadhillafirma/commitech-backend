<?php

namespace App\Http\Controllers;

use App\Models\HasilWawancara;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EksporController extends Controller
{
    public function generate()
    {
        try {
            $namaFile = 'hasil_seleksi_' . date('Y-m-d_His') . '.xlsx';
            $pathFile = $this->buatFileExcel($namaFile);

            return response()->json([
                'sukses' => true,
                'pesan' => 'File berhasil digenerate',
                'data' => [
                    'url_file' => url('storage/ekspor/' . $namaFile),
                    'nama_file' => $namaFile
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal generate file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unduh()
    {
        try {
            $namaFile = 'hasil_seleksi_' . date('Y-m-d_His') . '.xlsx';
            $pathFile = $this->buatFileExcel($namaFile);

            return response()->download($pathFile, $namaFile, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Gagal download file: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buatFileExcel($namaFile)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Seleksi');

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $headers = ['No', 'Nama', 'NIM', 'Divisi', 'Status', 'Tanggal Wawancara', 'Alasan Penolakan'];
        $kolom = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($kolom . '1', $header);
            $kolom++;
        }
        $sheet->getStyle('A1:G1')->applyFromArray($styleHeader);

        $hasil = HasilWawancara::with('peserta')
            ->orderBy('status', 'asc')
            ->get();

        $baris = 2;
        $no = 1;
        foreach ($hasil as $h) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $h->peserta->nama ?? '-');
            $sheet->setCellValue('C' . $baris, $h->peserta->nim ?? '-');
            $sheet->setCellValue('D' . $baris, $h->divisi ?? '-');
            $sheet->setCellValue('E' . $baris, ucfirst($h->status));
            $sheet->setCellValue('F' . $baris, $h->waktu_wawancara?->format('d M Y H:i') ?? '-');
            $sheet->setCellValue('G' . $baris, $h->alasan ?? '-');

            if ($h->status === 'diterima') {
                $sheet->getStyle('E' . $baris)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
            } elseif ($h->status === 'ditolak') {
                $sheet->getStyle('E' . $baris)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
            }

            $baris++;
            $no++;
        }

        foreach (range('A', 'G') as $kol) {
            $sheet->getColumnDimension($kol)->setAutoSize(true);
        }

        $barisTerakhir = $baris - 1;
        if ($barisTerakhir >= 2) {
            $sheet->getStyle('A2:G' . $barisTerakhir)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
        }

        $sheetRingkasan = $spreadsheet->createSheet();
        $sheetRingkasan->setTitle('Ringkasan');

        $diterima = $hasil->where('status', 'diterima')->count();
        $ditolak = $hasil->where('status', 'ditolak')->count();
        $pending = $hasil->where('status', 'pending')->count();
        $total = $hasil->count();

        $sheetRingkasan->setCellValue('A1', 'RINGKASAN HASIL SELEKSI');
        $sheetRingkasan->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheetRingkasan->setCellValue('A3', 'Total Peserta');
        $sheetRingkasan->setCellValue('B3', $total);

        $sheetRingkasan->setCellValue('A4', 'Diterima');
        $sheetRingkasan->setCellValue('B4', $diterima);

        $sheetRingkasan->setCellValue('A5', 'Ditolak');
        $sheetRingkasan->setCellValue('B5', $ditolak);

        $sheetRingkasan->setCellValue('A6', 'Pending');
        $sheetRingkasan->setCellValue('B6', $pending);

        $sheetRingkasan->getColumnDimension('A')->setWidth(20);
        $sheetRingkasan->getColumnDimension('B')->setWidth(15);

        // Simpan file
        $pathEkspor = storage_path('app/public/ekspor');
        if (!file_exists($pathEkspor)) {
            mkdir($pathEkspor, 0755, true);
        }

        $pathFile = $pathEkspor . '/' . $namaFile;
        $writer = new Xlsx($spreadsheet);
        $writer->save($pathFile);

        return $pathFile;
    }
}
