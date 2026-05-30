<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountType;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            [
                'name' => 'Bank',
                'description' => 'Bank account',
                'icon_url' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Mobile Money',
                'description' => 'Mobile money account',
                'icon_url' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Cash',
                'description' => 'Cash account',
                'icon_url' => null,
                'is_active' => true,
            ],
        ];

        foreach ($accountTypes as $accountType) {
            AccountType::firstOrCreate(
                ['name' => $accountType['name']],
                $accountType
            );
        }
    }
}
