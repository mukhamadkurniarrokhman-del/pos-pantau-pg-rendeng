<?php

namespace Database\Seeders;

use App\Models\Kebun;
use App\Models\Kontrak;
use App\Models\Petani;
use Illuminate\Database\Seeder;

class KontrakSeeder extends Seeder
{
    public function run(): void
    {
        $musim = config('app.musim_giling', env('MUSIM_GILING', '2026'));

        $data = [
            [
                'petani' => ['kode' => 'PTN-001', 'nama' => 'Pak Suparno',   'wa' => '081335551001', 'desa' => 'Gribig',     'kec' => 'Gebog',     'kab' => 'Kudus'],
                'kebun'  => ['kode' => 'KBN-001', 'nama' => 'Kebun Gribig Blok A', 'luas' => 2.5, 'blok' => 'A-01'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00001', 'tonase' => 175, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-002', 'nama' => 'Bu Sumiati',    'wa' => '081335551002', 'desa' => 'Jekulo',     'kec' => 'Jekulo',    'kab' => 'Kudus'],
                'kebun'  => ['kode' => 'KBN-002', 'nama' => 'Kebun Jekulo Utara', 'luas' => 1.8, 'blok' => 'B-03'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00002', 'tonase' => 130, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-003', 'nama' => 'Pak Harjono',   'wa' => '081335551003', 'desa' => 'Welahan',    'kec' => 'Welahan',   'kab' => 'Jepara'],
                'kebun'  => ['kode' => 'KBN-003', 'nama' => 'Kebun Welahan 1','luas' => 3.0, 'blok' => 'C-07'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00003', 'tonase' => 210, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-004', 'nama' => 'Pak Wahid',     'wa' => '081335551004', 'desa' => 'Margoyoso',  'kec' => 'Margoyoso', 'kab' => 'Pati'],
                'kebun'  => ['kode' => 'KBN-004', 'nama' => 'Kebun Margoyoso','luas' => 2.0, 'blok' => 'D-02'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00004', 'tonase' => 145, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-005', 'nama' => 'Bu Yuliani',    'wa' => '081335551005', 'desa' => 'Todanan',    'kec' => 'Todanan',   'kab' => 'Blora'],
                'kebun'  => ['kode' => 'KBN-005', 'nama' => 'Kebun Todanan Selatan','luas' => 4.2, 'blok' => 'E-11'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00005', 'tonase' => 295, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-006', 'nama' => 'Pak Rusdi',     'wa' => '081335551006', 'desa' => 'Purwodadi',  'kec' => 'Purwodadi', 'kab' => 'Grobogan'],
                'kebun'  => ['kode' => 'KBN-006', 'nama' => 'Kebun Purwodadi Tengah','luas' => 2.7, 'blok' => 'F-04'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00006', 'tonase' => 190, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-007', 'nama' => 'Pak Sutrisno',  'wa' => '081335551007', 'desa' => 'Lasem',      'kec' => 'Lasem',     'kab' => 'Rembang'],
                'kebun'  => ['kode' => 'KBN-007', 'nama' => 'Kebun Lasem',   'luas' => 2.3, 'blok' => 'G-05'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00007', 'tonase' => 165, 'harga' => 650000],
            ],
            [
                'petani' => ['kode' => 'PTN-008', 'nama' => 'Bu Endang',     'wa' => '081335551008', 'desa' => 'Japah',      'kec' => 'Japah',     'kab' => 'Blora'],
                'kebun'  => ['kode' => 'KBN-008', 'nama' => 'Kebun Japah Barat','luas' => 3.5, 'blok' => 'H-09'],
                'kontrak'=> ['nomor' => 'KTR-PGR-2026-00008', 'tonase' => 245, 'harga' => 650000],
            ],
        ];

        foreach ($data as $row) {
            $petani = Petani::updateOrCreate(
                ['kode_petani' => $row['petani']['kode']],
                [
                    'nama' => $row['petani']['nama'],
                    'no_wa' => $row['petani']['wa'],
                    'alamat' => "Dsn. {$row['petani']['desa']}, Desa {$row['petani']['desa']}",
                    'desa' => $row['petani']['desa'],
                    'kecamatan' => $row['petani']['kec'],
                    'kabupaten' => $row['petani']['kab'],
                    'kelompok_tani' => 'KT Rendeng Makmur',
                    'is_active' => true,
                ]
            );

            $kebun = Kebun::updateOrCreate(
                ['kode_kebun' => $row['kebun']['kode']],
                [
                    'nama' => $row['kebun']['nama'],
                    'petani_id' => $petani->id,
                    'luas_hektar' => $row['kebun']['luas'],
                    'desa' => $row['petani']['desa'],
                    'kecamatan' => $row['petani']['kec'],
                    'kabupaten' => $row['petani']['kab'],
                    'nomor_blok' => $row['kebun']['blok'],
                ]
            );

            Kontrak::updateOrCreate(
                ['nomor_kontrak' => $row['kontrak']['nomor']],
                [
                    'petani_id' => $petani->id,
                    'kebun_id' => $kebun->id,
                    'musim_giling' => $musim,
                    'tanggal_kontrak' => now()->subMonths(3)->toDateString(),
                    'tanggal_mulai_panen' => now()->subDays(30)->toDateString(),
                    'tanggal_akhir_panen' => now()->addMonths(4)->toDateString(),
                    'estimasi_tonase' => $row['kontrak']['tonase'],
                    'harga_per_ton' => $row['kontrak']['harga'],
                    'status' => 'aktif',
                ]
            );
        }
    }
}
