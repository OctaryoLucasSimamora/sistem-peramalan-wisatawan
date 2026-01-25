<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TouristData;
use App\Models\Daerah;

class TouristDataSeeder extends Seeder
{
    // Daftar daerah dengan kode unik
    private $daerahList = [
        // Format: [nama_daerah, kode_daerah, deskripsi]
        ['Surabaya', 'SUB', 'Ibu Kota Provinsi Jawa Timur'],
        ['Malang', 'MLG', 'Kota Pendidikan dan Pariwisata'],
        ['Sidoarjo', 'SDA', 'Kawasan Industri'],
        ['Kediri', 'KDR', 'Kota Rokok'],
        ['Mojokerto', 'MJK', 'Kota Sejarah'],
        ['Jember', 'JBR', 'Kota Tapal Kuda'],
        ['Banyuwangi', 'BWI', 'Kota Sunrise of Java'],
        ['Blitar', 'BLT', 'Kota Proklamator'],
        ['Pasuruan', 'PSR', 'Kota Santri'],
        ['Probolinggo', 'PBL', 'Kota Mangga'],
        ['Madiun', 'MDN', 'Kota Gadis'],
        ['Lamongan', 'LMG', 'Kota Soto'],
        ['Gresik', 'GRS', 'Kota Industri'],
        ['Tuban', 'TBN', 'Kota Wali'],
        ['Bojonegoro', 'BJG', 'Kota Ledre'],
        ['Lumajang', 'LMJ', 'Kota Pisang'],
        ['Bondowoso', 'BDW', 'Kota Tape'],
        ['Situbondo', 'SIT', 'Kota Santan'],
        ['Tulungagung', 'TLG', 'Kota Marmer'],
        ['Trenggalek', 'TRK', 'Kota Kecap'],
        ['Ponorogo', 'PNG', 'Kota Reyog'],
        ['Pacitan', 'PCT', 'Kota 1001 Goa'],
        ['Magetan', 'MGT', 'Kota Dodol'],
        ['Ngawi', 'NGW', 'Kota Bendo'],
        ['Nganjuk', 'NJK', 'Kota Angin'],
        ['Bangkalan', 'BKL', 'Kota Madura Barat'],
        ['Sampang', 'SPG', 'Kota Madura Tengah'],
        ['Pamekasan', 'PMK', 'Kota Batik'],
        ['Sumenep', 'SMP', 'Kota Kerapan Sapi']
    ];

    private $bulanList = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    public function run(): void
    {
        echo "🚀 Memulai seeding database...\n";
        echo "===============================\n";
        
        // 0. NONAKTIFKAN FOREIGN KEY CHECK sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Kosongkan tabel (dengan truncate agar reset auto increment)
        echo "🗑️  Mengosongkan tabel...\n";
        TouristData::truncate();
        Daerah::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "✅ Tabel berhasil dikosongkan\n\n";

        // 1. SEED DAERAH DULU
        echo "📋 Menyimpan data daerah...\n";
        foreach ($this->daerahList as $daerah) {
            Daerah::create([
                'nama_daerah' => $daerah[0],
                'kode_daerah' => $daerah[1],
                'deskripsi' => $daerah[2],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        echo "✅ " . count($this->daerahList) . " daerah berhasil disimpan\n\n";

        // 2. SEED TOURIST DATA (setelah daerah ada)
        echo "📊 Menyimpan data wisatawan...\n";
        $dataCount = 0;
        $tahunMulai = 2023;
        $tahunSelesai = 2024;
        
        $allData = []; // Kumpulkan dulu, insert sekaligus (lebih cepat)
        
        for ($tahun = $tahunMulai; $tahun <= $tahunSelesai; $tahun++) {
            foreach ($this->daerahList as $daerah) {
                foreach ($this->bulanList as $bulan) {
                    // Logika jumlah wisatawan
                    $baseAmount = match($bulan) {
                        'Juni', 'Juli', 'Desember' => 35000, // liburan sekolah
                        'Januari', 'Agustus' => 30000,       // tahun baru & HUT RI
                        default => 20000
                    };
                    
                    // Faktor berdasarkan daerah
                    $daerahFactor = match($daerah[0]) {
                        'Surabaya', 'Malang' => 1.5,  // daerah besar
                        'Banyuwangi', 'Jember' => 1.3, // daerah wisata
                        default => 1.0
                    };
                    
                    // Hitung jumlah dengan variasi
                    $jumlah = intval($baseAmount * $daerahFactor) + rand(-2000, 2000);
                    
                    $allData[] = [
                        'daerah' => $daerah[0],
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'jumlah' => $jumlah,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $dataCount++;
                    
                    // Insert per 100 data untuk efisiensi
                    if (count($allData) >= 100) {
                        TouristData::insert($allData);
                        $allData = [];
                    }
                }
            }
            
            echo "📅 Data tahun $tahun: " . (count($this->daerahList) * 12) . " record\n";
        }
        
        // Insert sisa data
        if (!empty($allData)) {
            TouristData::insert($allData);
        }
        
        echo "\n✅ Total data wisatawan: $dataCount record\n\n";

        // 3. VERIFIKASI DATA
        echo "🔍 Verifikasi data...\n";
        echo "-------------------\n";
        
        // Hitung total data
        $totalDaerah = Daerah::count();
        $totalTouristData = TouristData::count();
        
        echo "- Total Daerah: $totalDaerah\n";
        echo "- Total Data Wisatawan: $totalTouristData\n";
        
        // Cek relasi
        $sampleDaerah = Daerah::first();
        if ($sampleDaerah) {
            $countData = $sampleDaerah->touristData()->count();
            echo "- Data untuk {$sampleDaerah->nama_daerah}: $countData record\n";
        }
        
        // Contoh data
        echo "\n📋 Contoh data wisatawan:\n";
        $samples = TouristData::with(['daerahRel'])
                    ->orderBy('tahun', 'desc')
                    ->orderByRaw("FIELD(bulan, '" . implode("','", $this->bulanList) . "')")
                    ->limit(3)
                    ->get();
        
        foreach ($samples as $sample) {
            echo "  • {$sample->daerah}, {$sample->tahun} {$sample->bulan}: " . 
                 number_format($sample->jumlah, 0, ',', '.') . " wisatawan\n";
        }

        echo "\n🎉 SEEDER BERHASIL DIEKSEKUSI!\n";
        echo "===============================\n";
    }
}