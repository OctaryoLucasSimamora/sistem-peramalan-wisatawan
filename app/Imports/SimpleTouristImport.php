<?php

namespace App\Imports;

use App\Models\TouristData;
use App\Models\Daerah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SimpleTouristImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        \Log::info("Starting import with " . $rows->count() . " rows");
        
        foreach ($rows as $index => $row) {
            try {
                // DEBUG: Tampilkan data row
                // \Log::info("Row {$index}:", $row->toArray());
                
                // 1. AMBIL DATA DENGAN FLEXIBLE HEADER
                $daerah = $this->getValue($row, ['daerah', 'nama_daerah', 'nama daerah', 'daerah']);
                $tahun = $this->getValue($row, ['tahun', 'year', 'thn']);
                $bulan = $this->getValue($row, ['bulan', 'month', 'bln']);
                $jumlah = $this->getValue($row, ['jumlah', 'jml', 'total', 'wisatawan', 'pengunjung']);
                
                // 2. VALIDASI DATA
                if (empty($daerah) || empty($tahun) || empty($bulan) || empty($jumlah)) {
                    $this->skippedCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Data tidak lengkap";
                    continue;
                }
                
                // 3. NORMALISASI
                $daerah = trim($daerah);
                $tahun = (int) $tahun;
                $bulan = $this->normalizeBulan(trim($bulan));
                $jumlah = $this->convertToInteger($jumlah);
                
                if ($jumlah <= 0) {
                    $this->skippedCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Jumlah {$jumlah} tidak valid";
                    continue;
                }
                
                // 4. CEK/CREATE DAERAH
                if (!$this->ensureDaerahExists($daerah)) {
                    $this->skippedCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Gagal membuat daerah '{$daerah}'";
                    continue;
                }
                
                // 5. CREATE DATA
                TouristData::create([
                    'daerah' => $daerah,
                    'tahun'  => $tahun,
                    'bulan'  => $bulan,
                    'jumlah' => $jumlah,
                ]);
                
                $this->importedCount++;
                
                \Log::info("Imported: {$daerah}, {$tahun} {$bulan}, {$jumlah}");
                
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        \Log::info("Import finished. Imported: {$this->importedCount}, Skipped: {$this->skippedCount}");
    }
    
    private function getValue($row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Coba berbagai case: 'jumlah', 'Jumlah', 'JUMLAH'
            $lowerKey = strtolower($key);
            
            foreach ($row->keys() as $rowKey) {
                if (strtolower($rowKey) === $lowerKey) {
                    return $row[$rowKey];
                }
            }
        }
        
        return null;
    }
    
    private function normalizeBulan(string $bulan): string
    {
        $bulan = strtolower(trim($bulan));
        
        $monthMap = [
            'jan' => 'Januari', 'january' => 'Januari', 'januari' => 'Januari',
            'feb' => 'Februari', 'february' => 'Februari', 'februari' => 'Februari',
            'mar' => 'Maret', 'march' => 'Maret', 'maret' => 'Maret',
            'apr' => 'April', 'april' => 'April',
            'mei' => 'Mei', 'may' => 'Mei',
            'jun' => 'Juni', 'june' => 'Juni', 'juni' => 'Juni',
            'jul' => 'Juli', 'july' => 'Juli', 'juli' => 'Juli',
            'agu' => 'Agustus', 'aug' => 'Agustus', 'august' => 'Agustus', 'agustus' => 'Agustus',
            'sep' => 'September', 'september' => 'September',
            'okt' => 'Oktober', 'oct' => 'Oktober', 'october' => 'Oktober', 'oktober' => 'Oktober',
            'nov' => 'November', 'november' => 'November',
            'des' => 'Desember', 'dec' => 'Desember', 'december' => 'Desember', 'desember' => 'Desember',
        ];
        
        return $monthMap[$bulan] ?? ucwords(strtolower($bulan));
    }
    
    private function convertToInteger($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        if (is_string($value)) {
            // Hapus koma, titik, spasi
            $cleaned = preg_replace('/[^0-9]/', '', $value);
            return (int) $cleaned ?: 0;
        }
        
        return 0;
    }
    
    private function ensureDaerahExists(string $daerah): bool
    {
        // Cek dulu apakah sudah ada
        if (Daerah::where('nama_daerah', $daerah)->exists()) {
            return true;
        }
        
        // Buat baru
        try {
            $kode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $daerah), 0, 3));
            $tipe = str_starts_with($daerah, 'Kota') ? 'kota' : 
                   (str_starts_with($daerah, 'Kabupaten') ? 'kabupaten' : 'provinsi');
            
            // Cari kode unik
            $counter = 1;
            $originalKode = $kode;
            while (Daerah::where('kode_daerah', $kode)->exists()) {
                $kode = $originalKode . $counter;
                $counter++;
            }
            
            Daerah::create([
                'nama_daerah' => $daerah,
                'kode_daerah' => $kode,
                'tipe' => $tipe,
                'deskripsi' => 'Otomatis dibuat dari import',
            ]);
            
            \Log::info("Created new daerah: {$daerah}");
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Failed to create daerah {$daerah}: " . $e->getMessage());
            return false;
        }
    }
}