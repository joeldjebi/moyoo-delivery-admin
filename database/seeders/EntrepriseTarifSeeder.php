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

class EntrepriseTarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== GÉNÉRATION DES TARIFS OPTIMISÉS BASÉS SUR L'ENTREPRISE ===\n";

        // Récupérer l'entreprise cible (via config injectée par le contrôleur) ou fallback sur la première
        $entrepriseId = config('seed.entreprise_id');
        $entreprise = $entrepriseId ? Entreprise::find($entrepriseId) : Entreprise::first();
        if (!$entreprise) {
            echo "Aucune entreprise trouvée. Veuillez d'abord créer une entreprise.\n";
            return;
        }

        $communeDepart = $entreprise->commune;
        echo "Entreprise: {$entreprise->name}\n";
        echo "Commune de départ: {$communeDepart->libelle} (ID: {$communeDepart->id})\n";

        // Supprimer les anciens tarifs pour cette entreprise
        $deleted = TarifLivraison::where('entreprise_id', $entreprise->id)->delete();
        echo "Anciens tarifs supprimés: {$deleted}\n";

        // Récupérer toutes les données nécessaires pour cette entreprise uniquement
        $communes = Commune::all();
        $typeEngins = Type_engin::where('entreprise_id', $entreprise->id)->get();
        $modeLivraisons = Mode_livraison::where('entreprise_id', $entreprise->id)->get();
        $poids = Poid::where('entreprise_id', $entreprise->id)->get();
        $temps = Temp::where('entreprise_id', $entreprise->id)->get();

        $count = 0;
        $total = $communes->count() * $typeEngins->count() * $modeLivraisons->count() * $poids->count() * $temps->count();

        echo "Total de combinaisons à créer: {$total}\n";
        echo "Génération des tarifs de {$communeDepart->libelle} vers toutes les communes...\n\n";

        foreach ($communes as $communeDestination) {
            echo "Génération des tarifs vers {$communeDestination->libelle}...\n";

            foreach ($typeEngins as $typeEngin) {
                foreach ($modeLivraisons as $modeLivraison) {
                    foreach ($poids as $poid) {
                        foreach ($temps as $temp) {
                            // Calculer le montant de base
                            $amount = $this->calculateBaseAmount($communeDepart, $communeDestination, $typeEngin, $modeLivraison, $poid, $temp);

                            // Créer le tarif
                            TarifLivraison::create([
                                'entreprise_id' => $entreprise->id,
                                'commune_depart_id' => $communeDepart->id,
                                'commune_id' => $communeDestination->id,
                                'type_engin_id' => $typeEngin->id,
                                'mode_livraison_id' => $modeLivraison->id,
                                'poids_id' => $poid->id,
                                'temp_id' => $temp->id,
                                'amount' => $amount,
                                'created_by' => 1
                            ]);

                            $count++;

                            if ($count % 50 == 0) {
                                echo "  Créés: {$count}/{$total}\n";
                            }
                        }
                    }
                }
            }
        }

        echo "\n=== TERMINÉ ===\n";
        echo "Total de tarifs créés: {$count}\n";
        echo "Tarifs dans la base: " . TarifLivraison::where('entreprise_id', $entreprise->id)->count() . "\n";

        // Afficher les statistiques
        $this->displayStatistics($entreprise);
    }

    /**
     * Calculer le montant de base selon la logique métier
     */
    private function calculateBaseAmount($communeDepart, $communeDestination, $typeEngin, $modeLivraison, $poid, $temp)
    {
        $amount = 1000; // Montant de base

        // 1. Facteur distance (même commune = moins cher)
        if ($communeDepart->id == $communeDestination->id) {
            $amount += 0; // Même commune
        } else {
            // Distance différente selon les communes
            $distanceMultiplier = $this->getDistanceMultiplier($communeDepart->id, $communeDestination->id);
            $amount += $distanceMultiplier;
        }

        // 2. Facteur type d'engin
        switch ($typeEngin->id) {
            case 1: // Moto
                $amount += 0;
                break;
            case 2: // Voiture
                $amount += 500;
                break;
            case 3: // Camion
                $amount += 1000;
                break;
            case 4: // Moto (si doublon)
                $amount += 0;
                break;
        }

        // 3. Facteur mode de livraison
        if ($modeLivraison->id == 2) { // Express
            $amount += 500;
        }

        // 4. Facteur poids
        switch ($poid->id) {
            case 1: // 1 Kg
                $amount += 0;
                break;
            case 2: // 2 Kg
                $amount += 200;
                break;
            case 3: // 3 Kg
                $amount += 400;
                break;
        }

        // 5. Facteur période temporelle
        switch ($temp->id) {
            case 1: // Jour (6h-18h)
                $amount += 0;
                break;
            case 2: // Nuit (18h-6h)
                $amount += 300;
                break;
            case 3: // Week-end
                $amount += 500;
                break;
        }

        return $amount;
    }

    /**
     * Obtenir le multiplicateur de distance entre deux communes
     */
    private function getDistanceMultiplier($communeDepartId, $communeDestinationId)
    {
        // Matrice de distance simplifiée (en FCFA supplémentaires)
        $distanceMatrix = [
            1 => [1 => 0, 2 => 300, 3 => 500, 4 => 400, 5 => 600], // Abobo
            2 => [1 => 300, 2 => 0, 3 => 200, 4 => 100, 5 => 400], // Adjamé
            3 => [1 => 500, 2 => 200, 3 => 0, 4 => 300, 5 => 200], // Cocody
            4 => [1 => 400, 2 => 100, 3 => 300, 4 => 0, 5 => 500], // Attécoubé
            5 => [1 => 600, 2 => 400, 3 => 200, 4 => 500, 5 => 0], // Yopougon
        ];

        return $distanceMatrix[$communeDepartId][$communeDestinationId] ?? 500;
    }

    /**
     * Afficher les statistiques des tarifs créés
     */
    private function displayStatistics($entreprise)
    {
        echo "\n=== STATISTIQUES ===\n";

        $tarifs = TarifLivraison::where('entreprise_id', $entreprise->id)->get();

        echo "Tarif minimum: " . number_format($tarifs->min('amount'), 0, ',', ' ') . " FCFA\n";
        echo "Tarif maximum: " . number_format($tarifs->max('amount'), 0, ',', ' ') . " FCFA\n";
        echo "Tarif moyen: " . number_format($tarifs->avg('amount'), 0, ',', ' ') . " FCFA\n";

        echo "\n=== TARIFS PAR COMMUNE DE DESTINATION ===\n";
        $communes = Commune::all();
        foreach ($communes as $commune) {
            $count = $tarifs->where('commune_id', $commune->id)->count();
            $avgPrice = $tarifs->where('commune_id', $commune->id)->avg('amount');
            echo "{$entreprise->commune->libelle} → {$commune->libelle}: {$count} tarifs, prix moyen " .
                 number_format($avgPrice, 0, ',', ' ') . " FCFA\n";
        }
    }
}
