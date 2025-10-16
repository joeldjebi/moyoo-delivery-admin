<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TarifLivraison;
use App\Models\Commune;
use App\Models\Type_engin;
use App\Models\Mode_livraison;
use App\Models\Poid;
use App\Models\Temp;
use App\Models\Entreprise;

class TarifLivraisonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== CRÉATION DES TARIFS DE LIVRAISON ===\n";

        // Supprimer les tarifs existants
        TarifLivraison::truncate();
        echo "Anciens tarifs supprimés.\n";

        // Récupérer l'entreprise pour obtenir la commune de départ
        $entreprise = Entreprise::first();
        if (!$entreprise) {
            echo "Aucune entreprise trouvée. Veuillez d'abord créer une entreprise.\n";
            return;
        }

        $communeDepart = $entreprise->commune;
        echo "Commune de départ: {$communeDepart->libelle}\n";

        $communes = Commune::all();
        $typeEngins = Type_engin::all();
        $modeLivraisons = Mode_livraison::all();
        $poids = Poid::all();
        $temps = Temp::all();

        $count = 0;
        $total = $communes->count() * $typeEngins->count() * $modeLivraisons->count() * $poids->count() * $temps->count();

        echo "Total de combinaisons à créer: {$total}\n";

        foreach ($communes as $commune) {
            foreach ($typeEngins as $typeEngin) {
                foreach ($modeLivraisons as $modeLivraison) {
                    foreach ($poids as $poid) {
                        foreach ($temps as $temp) {
                            // Déterminer le montant selon la logique
                            $amount = 1000; // Montant de base

                            // Augmenter selon le type d'engin
                            if ($typeEngin->id == 2) { // Voiture
                                $amount += 500;
                            } elseif ($typeEngin->id == 3) { // Camion
                                $amount += 1000;
                            }

                            // Augmenter selon le mode de livraison
                            if ($modeLivraison->id == 2) { // Express
                                $amount += 500;
                            }

                            // Augmenter selon le poids
                            if ($poid->id == 2) { // 2 Kg
                                $amount += 200;
                            } elseif ($poid->id == 3) { // 3 Kg
                                $amount += 400;
                            }

                            // Augmenter selon la période temporelle
                            if ($temp->id == 2) { // Nuit
                                $amount += 300;
                            } elseif ($temp->id == 3) { // Week-end
                                $amount += 500;
                            }

                            // Créer le tarif
                            TarifLivraison::create([
                                'commune_depart_id' => $communeDepart->id,
                                'commune_id' => $commune->id,
                                'type_engin_id' => $typeEngin->id,
                                'mode_livraison_id' => $modeLivraison->id,
                                'poids_id' => $poid->id,
                                'temp_id' => $temp->id,
                                'amount' => $amount,
                                'created_by' => 1
                            ]);

                            $count++;

                            if ($count % 50 == 0) {
                                echo "Créés: {$count}/{$total}\n";
                            }
                        }
                    }
                }
            }
        }

        echo "=== TERMINÉ ===\n";
        echo "Total de tarifs créés: {$count}\n";
        echo "Tarifs dans la base: " . TarifLivraison::count() . "\n";

        // Afficher les statistiques
        echo "\n=== STATISTIQUES ===\n";
        echo "Tarif minimum: " . number_format(TarifLivraison::min('amount'), 0, ',', ' ') . " FCFA\n";
        echo "Tarif maximum: " . number_format(TarifLivraison::max('amount'), 0, ',', ' ') . " FCFA\n";
        echo "Tarif moyen: " . number_format(TarifLivraison::avg('amount'), 0, ',', ' ') . " FCFA\n";
    }
}
