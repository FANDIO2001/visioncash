<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'price_monthly_xaf' => 0,
                'price_annual_xaf' => 0,
                'max_accounts' => 2,
                'max_transactions_month' => 50,
                'max_categories' => 10,
                'max_budgets' => 3,
                'max_integrations' => 0,
                'export_pdf' => false,
                'export_excel' => false,
                'csv_import' => false,
                'recurring_transactions' => false,
                'forecast' => false,
                'history_months' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price_monthly_xaf' => 4990,
                'price_annual_xaf' => 49900,
                'max_accounts' => 10,
                'max_transactions_month' => 500,
                'max_categories' => 50,
                'max_budgets' => 20,
                'max_integrations' => 2,
                'export_pdf' => true,
                'export_excel' => true,
                'csv_import' => true,
                'recurring_transactions' => true,
                'forecast' => true,
                'history_months' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Entreprise',
                'price_monthly_xaf' => 14990,
                'price_annual_xaf' => 149900,
                'max_accounts' => null,
                'max_transactions_month' => null,
                'max_categories' => null,
                'max_budgets' => null,
                'max_integrations' => null,
                'export_pdf' => true,
                'export_excel' => true,
                'csv_import' => true,
                'recurring_transactions' => true,
                'forecast' => true,
                'history_months' => null,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
