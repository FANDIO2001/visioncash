<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', 'admin')->first() ?? User::first();

        if (!$owner) {
            return;
        }

        $defaults = [
            ['name' => 'Salaire', 'category_type' => 'income', 'color' => '#10b981'],
            ['name' => 'Freelance', 'category_type' => 'income', 'color' => '#3b82f6'],
            ['name' => 'Autres revenus', 'category_type' => 'income', 'color' => '#8b5cf6'],
            ['name' => 'Alimentation', 'category_type' => 'expense', 'color' => '#f59e0b'],
            ['name' => 'Transport', 'category_type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Logement', 'category_type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Santé', 'category_type' => 'expense', 'color' => '#ec4899'],
            ['name' => 'Loisirs', 'category_type' => 'expense', 'color' => '#14b8a6'],
            ['name' => 'Shopping', 'category_type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Autres dépenses', 'category_type' => 'expense', 'color' => '#64748b'],
        ];

        foreach ($defaults as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'is_default' => true],
                [
                    'user_id' => $owner->id,
                    'description' => null,
                    'icon' => null,
                    'color' => $category['color'],
                    'category_type' => $category['category_type'],
                    'is_active' => true,
                    'is_default' => true,
                ]
            );
        }
    }
}
