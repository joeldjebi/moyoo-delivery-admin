<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class StockModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le module de gestion de stock
        $module = Module::firstOrCreate(
            ['slug' => 'stock_management'],
            [
                'name' => 'Gestion de Stock',
                'description' => 'Module complet et minimal pour gérer les produits et leurs stocks',
                'icon' => 'ti ti-boxes',
                'category' => 'inventory',
                'is_active' => true,
                'is_optional' => true, // Module optionnel
                'price' => 5000.00, // Prix en XOF (à ajuster selon vos besoins)
                'currency' => 'XOF',
                'sort_order' => 10,
                'routes' => [
                    'categories.index',
                    'categories.create',
                    'categories.store',
                    'categories.show',
                    'categories.edit',
                    'categories.update',
                    'categories.destroy',
                    'products.index',
                    'products.create',
                    'products.store',
                    'products.show',
                    'products.edit',
                    'products.update',
                    'products.destroy',
                    'stocks.index',
                    'stocks.create',
                    'stocks.store',
                    'stocks.show',
                    'stocks.edit',
                    'stocks.update',
                    'stocks.destroy',
                    'stocks.adjust',
                    'stocks.entry',
                    'stocks.exit',
                    'stock-movements.index',
                    'stock-movements.show',
                    'stock-movements.by-product'
                ]
            ]
        );

        // Mettre à jour le prix et is_optional si le module existe déjà
        if (!$module->wasRecentlyCreated) {
            $module->update([
                'is_optional' => true,
                'price' => 5000.00,
                'currency' => 'XOF',
                'icon' => 'ti ti-boxes'
            ]);
        }

        $this->command->info('Module de gestion de stock créé avec succès !');
        $this->command->info('Slug: ' . $module->slug);
        $this->command->info('Nom: ' . $module->name);
        $this->command->info('Prix: ' . number_format($module->price, 0, ',', ' ') . ' ' . $module->currency);
        $this->command->info('Module optionnel: ' . ($module->is_optional ? 'Oui' : 'Non'));
    }
}
