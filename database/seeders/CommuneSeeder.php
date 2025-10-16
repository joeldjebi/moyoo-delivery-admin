<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Commune;
use App\Models\Ville;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer une ville par défaut si elle n'existe pas
        $ville = Ville::firstOrCreate(
            ['libelle' => 'Abidjan'],
            ['libelle' => 'Abidjan', 'created_by' => 'system']
        );

        // Créer les communes
        $communes = [
            ['libelle' => 'Cocody', 'ville_id' => $ville->id],
            ['libelle' => 'Adjamé', 'ville_id' => $ville->id],
            ['libelle' => 'Attécoubé', 'ville_id' => $ville->id],
            ['libelle' => 'Yopougon', 'ville_id' => $ville->id],
            ['libelle' => 'Marcory', 'ville_id' => $ville->id],
            ['libelle' => 'Plateau', 'ville_id' => $ville->id],
            ['libelle' => 'Treichville', 'ville_id' => $ville->id],
            ['libelle' => 'Koumassi', 'ville_id' => $ville->id],
            ['libelle' => 'Port-Bouët', 'ville_id' => $ville->id],
            ['libelle' => 'Anyama', 'ville_id' => $ville->id],
        ];

        foreach ($communes as $commune) {
            Commune::firstOrCreate(
                ['libelle' => $commune['libelle']],
                $commune
            );
        }
    }
}
