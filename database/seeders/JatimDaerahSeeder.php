<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Daerah;
use App\Models\TouristData;
use Illuminate\Support\Facades\DB;

class JatimDaerahSeeder extends Seeder
{
    // 39 DAERAH JATIM LENGKAP (sesuai Excel Anda)
    private $daerahJatim = [
        // KABUPATEN (29)
        ['Kabupaten Bangkalan', 'BKL', 'kabupaten', 'Kabupaten di Pulau Madura'],
        ['Kabupaten Banyuwangi', 'BWI', 'kabupaten', 'Kabupaten paling timur Jawa'],
        ['Kabupaten Blitar', 'BLT', 'kabupaten', 'Kabupaten Blitar'],
        ['Kabupaten Bojonegoro', 'BJG', 'kabupaten', 'Kabupaten Bojonegoro'],
        ['Kabupaten Bondowoso', 'BDW', 'kabupaten', 'Kabupaten Bondowoso'],
        ['Kabupaten Gresik', 'GRS', 'kabupaten', 'Kabupaten Gresik'],
        ['Kabupaten Jember', 'JBR', 'kabupaten', 'Kabupaten Jember'],
        ['Kabupaten Jombang', 'JBG', 'kabupaten', 'Kabupaten Jombang'],
        ['Kabupaten Kediri', 'KDR', 'kabupaten', 'Kabupaten Kediri'],
        ['Kabupaten Lamongan', 'LMG', 'kabupaten', 'Kabupaten Lamongan'],
        ['Kabupaten Lumajang', 'LMJ', 'kabupaten', 'Kabupaten Lumajang'],
        ['Kabupaten Madiun', 'MDN', 'kabupaten', 'Kabupaten Madiun'],
        ['Kabupaten Magetan', 'MGT', 'kabupaten', 'Kabupaten Magetan'],
        ['Kabupaten Malang', 'MLG', 'kabupaten', 'Kabupaten Malang'],
        ['Kabupaten Mojokerto', 'MJK', 'kabupaten', 'Kabupaten Mojokerto'],
        ['Kabupaten Nganjuk', 'NGK', 'kabupaten', 'Kabupaten Nganjuk'],
        ['Kabupaten Ngawi', 'NGW', 'kabupaten', 'Kabupaten Ngawi'],
        ['Kabupaten Pacitan', 'PCT', 'kabupaten', 'Kabupaten Pacitan'],
        ['Kabupaten Pamekasan', 'PMK', 'kabupaten', 'Kabupaten Pamekasan'],
        ['Kabupaten Pasuruan', 'PSN', 'kabupaten', 'Kabupaten Pasuruan'],
        ['Kabupaten Ponorogo', 'PNG', 'kabupaten', 'Kabupaten Ponorogo'],
        ['Kabupaten Probolinggo', 'PBL', 'kabupaten', 'Kabupaten Probolinggo'],
        ['Kabupaten Sampang', 'SPG', 'kabupaten', 'Kabupaten Sampang'],
        ['Kabupaten Sidoarjo', 'SDA', 'kabupaten', 'Kabupaten Sidoarjo'],
        ['Kabupaten Situbondo', 'SIT', 'kabupaten', 'Kabupaten Situbondo'],
        ['Kabupaten Sumenep', 'SMP', 'kabupaten', 'Kabupaten Sumenep'],
        ['Kabupaten Trenggalek', 'TRK', 'kabupaten', 'Kabupaten Trenggalek'],
        ['Kabupaten Tuban', 'TBN', 'kabupaten', 'Kabupaten Tuban'],
        ['Kabupaten Tulungagung', 'TLG', 'kabupaten', 'Kabupaten Tulungagung'],
        
        // KOTA (9)
        ['Kota Batu', 'BTU', 'kota', 'Kota Wisata Batu'],
        ['Kota Blitar', 'BLT', 'kota', 'Kota Blitar'],
        ['Kota Kediri', 'KDR', 'kota', 'Kota Kediri'],
        ['Kota Madiun', 'MDN', 'kota', 'Kota Madiun'],
        ['Kota Malang', 'MLG', 'kota', 'Kota Malang'],
        ['Kota Mojokerto', 'MJK', 'kota', 'Kota Mojokerto'],
        ['Kota Pasuruan', 'PSN', 'kota', 'Kota Pasuruan'],
        ['Kota Probolinggo', 'PBL', 'kota', 'Kota Probolinggo'],
        ['Kota Surabaya', 'SUB', 'kota', 'Ibu Kota Provinsi Jawa Timur'],
        
        // PROVINSI (1)
        ['PROVINSI JAWA TIMUR', 'JTM', 'provinsi', 'Data agregat seluruh Jawa Timur'],
    ];

    private $bulanList = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    public function run(): void
    {
        echo "🚀 Memulai seeding 39 Daerah Jawa Timur...\n";
        echo "===========================================\n";
        
        // NONAKTIFKAN FOREIGN KEY CHECK
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Kosongkan tabel
        echo "🗑️  Mengosongkan tabel...\n";
        TouristData::truncate();
        Daerah::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "✅ Tabel berhasil dikosongkan\n\n";

        // 1. SIMPAN 39 DAERAH
        echo "📋 Menyimpan 39 daerah Jawa Timur...\n";
        foreach ($this->daerahJatim as $daerah) {
            Daerah::create([
                'nama_daerah' => $daerah[0],
                'kode_daerah' => $daerah[1],
                'tipe' => $daerah[2],
                'deskripsi' => $daerah[3],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✅ {$daerah[0]}\n";
        }
        
        $kabCount = count(array_filter($this->daerahJatim, fn($d) => $d[2] === 'kabupaten'));
        $kotaCount = count(array_filter($this->daerahJatim, fn($d) => $d[2] === 'kota'));
        $provCount = count(array_filter($this->daerahJatim, fn($d) => $d[2] === 'provinsi'));
        
        echo "\n📊 Statistik Daerah:\n";
        echo "- Kabupaten: {$kabCount}\n";
        echo "- Kota: {$kotaCount}\n";
        echo "- Provinsi: {$provCount}\n";
        echo "- TOTAL: " . count($this->daerahJatim) . " daerah\n\n";

        // 2. BUAT DATA DUMMY WISATAWAN (optional, bisa skip jika mau import Excel langsung)
        echo "📊 Membuat data dummy wisatawan (2019-2024)...\n";
        $dataCount = 0;
        $allData = [];
        
        // Hanya buat data untuk beberapa daerah contoh
        $sampleDaerah = ['Kota Surabaya', 'Kota Malang', 'Kabupaten Malang', 'Kabupaten Banyuwangi'];
        
        for ($tahun = 2019; $tahun <= 2024; $tahun++) {
            foreach ($sampleDaerah as $namaDaerah) {
                foreach ($this->bulanList as $bulan) {
                    // Logika jumlah realistis
                    $baseAmount = match($namaDaerah) {
                        'Kota Surabaya' => rand(50000, 150000),
                        'Kota Malang' => rand(30000, 80000),
                        'Kabupaten Malang' => rand(20000, 50000),
                        'Kabupaten Banyuwangi' => rand(15000, 40000),
                        default => rand(10000, 30000)
                    };
                    
                    // Musiman: lebih tinggi di bulan liburan
                    if (in_array($bulan, ['Juni', 'Juli', 'Desember'])) {
                        $baseAmount *= 1.5;
                    }
                    
                    $allData[] = [
                        'daerah' => $namaDaerah,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'jumlah' => $baseAmount,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $dataCount++;
                    
                    // Insert per 100 data
                    if (count($allData) >= 100) {
                        TouristData::insert($allData);
                        $allData = [];
                    }
                }
            }
            echo "📅 Tahun {$tahun}: " . (count($sampleDaerah) * 12) . " record\n";
        }
        
        // Insert sisa data
        if (!empty($allData)) {
            TouristData::insert($allData);
        }
        
        echo "\n✅ Data dummy: {$dataCount} record untuk 4 daerah contoh\n\n";

        // 3. VERIFIKASI
        echo "🔍 Verifikasi data...\n";
        echo "-------------------\n";
        
        $totalDaerah = Daerah::count();
        $totalData = TouristData::count();
        
        echo "- Total Daerah di Database: {$totalDaerah}\n";
        echo "- Total Data Wisatawan: {$totalData}\n";
        
        // Cek beberapa daerah
        echo "\n📋 Contoh daerah yang tersimpan:\n";
        $samples = Daerah::inRandomOrder()->limit(5)->get();
        foreach ($samples as $d) {
            $dataCount = $d->touristData()->count();
            echo "- {$d->nama_daerah} ({$d->tipe}): {$dataCount} data\n";
        }
        
        // Cek foreign key
        echo "\n🔗 Test foreign key constraint:\n";
        try {
            TouristData::create([
                'daerah' => 'DAERAH_TIDAK_ADA',
                'tahun' => 2025,
                'bulan' => 'Januari',
                'jumlah' => 1000
            ]);
            echo "❌ ERROR: Seharusnya gagal!\n";
        } catch (\Exception $e) {
            echo "✅ BERHASIL: Foreign key aktif, data invalid ditolak\n";
        }

        echo "\n🎉 SEEDER 39 DAERAH JATIM BERHASIL!\n";
        echo "===================================\n";
        echo "Sekarang database siap untuk import Excel Anda!\n";
        echo "Format daerah sudah match: 'Kabupaten Bangkalan', 'Kota Surabaya', dll.\n";
    }
}