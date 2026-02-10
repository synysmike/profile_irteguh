<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1-1000', 'name' => 'Kas', 'type' => 'kas', 'order' => 1],
            ['code' => '1-1100', 'name' => 'Piutang Usaha', 'type' => 'piutang', 'order' => 2],
            ['code' => '2-2000', 'name' => 'Hutang Usaha', 'type' => 'hutang', 'order' => 3],
            ['code' => '3-3000', 'name' => 'Modal Pemilik', 'type' => 'modal', 'order' => 4],
            ['code' => '4-4000', 'name' => 'Pendapatan Usaha', 'type' => 'pendapatan', 'order' => 5],
            ['code' => '5-5000', 'name' => 'Beban Operasional', 'type' => 'beban', 'order' => 6],
        ];

        foreach ($accounts as $i => $data) {
            ChartOfAccount::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
