<?php

namespace Database\Seeders;

use App\Models\TokenPackage;
use Illuminate\Database\Seeder;

class TokenPackageSeeder extends Seeder
{
    public function run(): void
    {
        $paket = [
            ['nama' => '1 Token', 'jumlah_token' => 1, 'harga' => 5000],
            ['nama' => '5 Token', 'jumlah_token' => 5, 'harga' => 20000],
            ['nama' => '8 Token', 'jumlah_token' => 8, 'harga' => 35000],
            ['nama' => '10 Token', 'jumlah_token' => 10, 'harga' => 40000],
        ];

        foreach ($paket as $p) {
            TokenPackage::updateOrCreate(['jumlah_token' => $p['jumlah_token']], $p);
        }
    }
}
