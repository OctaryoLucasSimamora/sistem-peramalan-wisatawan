<?php

namespace App\Imports;

use App\Models\TouristData;
use App\Models\Daerah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Validation\Rule;

class TouristImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError
{
    use SkipsErrors; // Trait ini sudah punya property $errors
    
    private $successCount = 0;
    private $skipCount = 0;
    private $importErrors = []; // GANTI NAMA DARI $errors JADI $importErrors
    
    public function model(array $row)
    {
        // Debug: lihat struktur row
        // \Log::info('Processing row:', $row);
        
        // Normalize key names (case insensitive)
        $normalizedRow = $this->normalizeKeys($row);
        
        // Cek apakah semua kolom required ada dan tidak kosong
        if (!$this->hasValidData($normalizedRow)) {
            $this->skipCount++;
            return null;
        }

        try {
            $daerah = trim($normalizedRow['daerah']);
            $tahun = (int) $normalizedRow['tahun'];
            $bulan = trim($normalizedRow['bulan']);
            $jumlah = $normalizedRow['jumlah'];
            
            // Normalisasi bulan
            $bulan = $this->normalizeBulan($bulan);
            
            // Cek apakah daerah valid
            if (!$this->isValidDaerah($daerah)) {
                $this->importErrors[] = "Daerah '{$daerah}' tidak ditemukan (baris dengan tahun: {$tahun}, bulan: {$bulan})";
                $this->skipCount++;
                return null;
            }

            // Convert jumlah ke integer
            $jumlah = $this->convertToInteger($jumlah);
            
            // Validasi jumlah minimal
            if ($jumlah <= 0) {
                $this->importErrors[] = "Jumlah {$jumlah} tidak valid untuk {$daerah} {$bulan} {$tahun}";
                $this->skipCount++;
                return null;
            }

            $this->successCount++;
            
            return new TouristData([
                'daerah' => $daerah,
                'tahun'  => $tahun,
                'bulan'  => $bulan,
                'jumlah' => $jumlah,
            ]);
            
        } catch (\Exception $e) {
            $this->importErrors[] = "Error processing row: " . $e->getMessage();
            $this->skipCount++;
            return null;
        }
    }

    /**
     * Normalize array keys to lowercase
     */
    private function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            // Handle berbagai format key
            $normalizedKey = strtolower(trim($key));
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }

    /**
     * Check if row has valid data
     */
    private function hasValidData(array $row): bool
    {
        $required = ['daerah', 'tahun', 'bulan', 'jumlah'];
        
        foreach ($required as $column) {
            // Cek apakah kolom ada
            if (!isset($row[$column])) {
                return false;
            }
            
            // Cek apakah kosong atau null
            $value = $row[$column];
            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                return false;
            }
            
            // Validasi khusus untuk jumlah
            if ($column === 'jumlah') {
                // Coba konversi ke numeric
                $numericValue = $this->convertToInteger($value);
                if ($numericValue === 0 && $value != '0') {
                    return false; // Bukan angka yang valid
                }
            }
        }
        
        return true;
    }

    /**
     * Convert berbagai format jumlah ke integer
     */
    private function convertToInteger($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        if (is_string($value)) {
            // Hapus karakter non-numeric kecuali titik (untuk decimal)
            $cleaned = preg_replace('/[^0-9\.]/', '', $value);
            
            // Handle decimal
            if (strpos($cleaned, '.') !== false) {
                return (int) round((float) $cleaned);
            }
            
            return (int) $cleaned;
        }
        
        return 0;
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

    private function isValidDaerah(string $daerah): bool
    {
        return Daerah::where('nama_daerah', $daerah)->exists();
    }

    public function rules(): array
    {
        return [
            'daerah' => 'required',
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2050'
            ],
            'bulan' => 'required',
            'jumlah' => [
                'required',
                'numeric',
                'min:0'
            ]
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'daerah.required' => 'Kolom daerah harus diisi',
            'tahun.required' => 'Kolom tahun harus diisi',
            'tahun.integer' => 'Tahun harus angka',
            'tahun.min' => 'Tahun minimal 2000',
            'tahun.max' => 'Tahun maksimal 2050',
            'bulan.required' => 'Kolom bulan harus diisi',
            'jumlah.required' => 'Kolom jumlah harus diisi',
            'jumlah.numeric' => 'Jumlah harus angka',
            'jumlah.min' => 'Jumlah minimal 0',
        ];
    }

    // Get statistics
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
    
    public function getSkipCount(): int
    {
        return $this->skipCount;
    }
    
    public function getImportErrors(): array // GANTI NAMA METHOD
    {
        return $this->importErrors;
    }
}