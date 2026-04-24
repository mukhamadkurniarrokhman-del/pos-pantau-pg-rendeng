<?php

namespace Database\Seeders;

use App\Models\PosPantau;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Admin pusat
        User::updateOrCreate(
            ['nip' => 'ADM-001'],
            [
                'name' => 'Admin PG Rendeng',
                'email' => 'admin@pgrendeng.co.id',
                'password' => Hash::make('admin123'),
                'phone' => '081234567890',
                'role' => 'admin',
                'pos_pantau_id' => null,
                'is_active' => true,
            ]
        );

        // 1 Supervisor (bisa lihat semua pos, tapi bukan admin)
        User::updateOrCreate(
            ['nip' => 'SPV-001'],
            [
                'name' => 'Supervisor Pemantauan',
                'email' => 'supervisor@pgrendeng.co.id',
                'password' => Hash::make('supervisor123'),
                'phone' => '081234567891',
                'role' => 'supervisor',
                'pos_pantau_id' => null,
                'is_active' => true,
            ]
        );

        // 1 petugas per pos
        $petugasData = [
            ['nip' => 'PTG-JPR-01', 'nama' => 'Agus Setiawan',     'kode_pos' => 'JPR'],
            ['nip' => 'PTG-PTI-01', 'nama' => 'Budi Santoso',      'kode_pos' => 'PTI'],
            ['nip' => 'PTG-RBG-01', 'nama' => 'Cahyo Wibowo',      'kode_pos' => 'RBG'],
            ['nip' => 'PTG-JPH-01', 'nama' => 'Dedi Kurniawan',    'kode_pos' => 'JPH'],
            ['nip' => 'PTG-TDN-01', 'nama' => 'Eko Prasetyo',      'kode_pos' => 'TDN'],
            ['nip' => 'PTG-GBG-01', 'nama' => 'Fajar Nugroho',     'kode_pos' => 'GBG'],
        ];

        foreach ($petugasData as $p) {
            $pos = PosPantau::where('kode', $p['kode_pos'])->first();

            if (! $pos) {
                continue;
            }

            User::updateOrCreate(
                ['nip' => $p['nip']],
                [
                    'name' => $p['nama'],
                    'email' => strtolower(str_replace(['-', ' '], '.', $p['nip'])) . '@pgrendeng.co.id',
                    'password' => Hash::make('petugas123'),
                    'phone' => '0812' . rand(10000000, 99999999),
                    'role' => 'petugas_pos',
                    'pos_pantau_id' => $pos->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
