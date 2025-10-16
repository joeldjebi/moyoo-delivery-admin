<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entreprise;
use App\Models\Commune;

class EntrepriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== CRÉATION D'UNE ENTREPRISE DE TEST ===\n";

        // Récupérer la première commune disponible
        $commune = Commune::first();

        if (!$commune) {
            echo "Aucune commune trouvée. Veuillez d'abord exécuter CommuneSeeder.\n";
            return;
        }

        // Créer une entreprise de test pour l'utilisateur ID 1
        $entreprise = Entreprise::firstOrCreate(
            ['created_by' => 1],
            [
                'name' => 'MOYOO Delivery Services',
                'mobile' => '+225 07 12 34 56 78',
                'email' => 'contact@moyoo-delivery.com',
                'adresse' => 'Cocody, Abidjan, Côte d\'Ivoire',
                'commune_id' => $commune->id,
                'statut' => 1,
                'logo' => null,
                'created_by' => 1
            ]
        );

        if ($entreprise->wasRecentlyCreated) {
            echo "Entreprise créée avec succès : {$entreprise->name}\n";
            echo "Commune de départ : {$commune->libelle}\n";
        } else {
            echo "Entreprise déjà existante : {$entreprise->name}\n";
        }

        echo "=== TERMINÉ ===\n";
    }
}
