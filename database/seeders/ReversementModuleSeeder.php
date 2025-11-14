<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class ReversementModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le module de gestion de reversements
        $module = Module::firstOrCreate(
            ['slug' => 'reversement_management'],
            [
                'name' => 'Gestion des Reversements',
                'description' => 'Module pour gérer les reversements, balances et historique des marchands',
                'icon' => 'ti ti-wallet',
                'category' => 'finance',
                'is_active' => true,
                'is_optional' => false, // Module de base (inclus par défaut)
                'price' => 0.00, // Gratuit car module de base
                'currency' => 'XOF',
                'sort_order' => 15,
                'routes' => [
                    'reversements.index',
                    'reversements.create',
                    'reversements.store',
                    'reversements.show',
                    'reversements.validate',
                    'reversements.cancel',
                    'balances.index',
                    'historique.balances'
                ]
            ]
        );

        // Mettre à jour les propriétés si le module existe déjà
        if (!$module->wasRecentlyCreated) {
            $module->update([
                'is_optional' => false,
                'is_active' => true,
                'icon' => 'ti ti-wallet',
                'category' => 'finance'
            ]);
        }

        $this->command->info('Module de gestion des reversements créé avec succès !');
        $this->command->info('Slug: ' . $module->slug);
        $this->command->info('Nom: ' . $module->name);
        $this->command->info('Prix: ' . number_format($module->price, 0, ',', ' ') . ' ' . $module->currency);
        $this->command->info('Module optionnel: ' . ($module->is_optional ? 'Oui' : 'Non'));
    }
}
