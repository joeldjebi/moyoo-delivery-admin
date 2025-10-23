<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Historique_livraison;
use App\Models\Colis;
use App\Models\PackageColis;
use App\Models\Livreur;
use App\Models\Engin;
use App\Models\Zone;
use App\Models\Commune;
use Illuminate\Support\Facades\DB;

class CreateTodayTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-today-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer des données de test pour aujourd\'hui';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📅 Création de données de test pour aujourd\'hui...');

        DB::beginTransaction();

        try {
            // Récupérer les données nécessaires
            $livreur = Livreur::first();
            $engin = Engin::first();
            $zone = Zone::first();
            $commune = Commune::first();

            if (!$livreur || !$engin || !$zone || !$commune) {
                $this->error('❌ Données de base manquantes (livreur, engin, zone, commune)');
                return;
            }

            // Créer un package de colis pour aujourd'hui
            $package = PackageColis::create([
                'entreprise_id' => 1,
                'numero_package' => PackageColis::generatePackageNumber(),
                'marchand_id' => 1,
                'boutique_id' => 1,
                'nombre_colis' => 3,
                'communes_selected' => [$commune->id],
                'colis_ids' => [],
                'livreur_id' => $livreur->id,
                'engin_id' => $engin->id,
                'statut' => 'en_attente',
                'created_by' => 1
            ]);

            // Créer 3 colis pour aujourd'hui
            $colisIds = [];
            for ($i = 1; $i <= 3; $i++) {
                $colis = Colis::create([
                    'entreprise_id' => 1,
                    'uuid' => \Str::uuid(),
                    'code' => 'CLIS-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'montant_a_encaisse' => 5000 + ($i * 1000),
                    'prix_de_vente' => 5000 + ($i * 1000),
                    'numero_facture' => 'FACT-' . $i,
                    'nom_client' => 'Client Test ' . $i,
                    'telephone_client' => '+225070000000' . $i,
                    'adresse_client' => 'Adresse Test ' . $i,
                    'note_client' => 'Note test ' . $i,
                    'status' => 2, // Livré
                    'zone_id' => $zone->id,
                    'commune_id' => $commune->id,
                    'package_colis_id' => $package->id,
                    'livreur_id' => $livreur->id,
                    'engin_id' => $engin->id,
                    'created_by' => 1
                ]);

                $colisIds[] = $colis->id;

                // Créer l'historique de livraison pour aujourd'hui
                Historique_livraison::create([
                    'entreprise_id' => 1,
                    'package_colis_id' => $package->id,
                    'livraison_id' => $i,
                    'status' => 'livre',
                    'colis_id' => $colis->id,
                    'livreur_id' => $livreur->id,
                    'montant_a_encaisse' => $colis->montant_a_encaisse,
                    'prix_de_vente' => $colis->prix_de_vente,
                    'montant_de_la_livraison' => 1500 + ($i * 200), // Frais variables
                    'created_by' => '1',
                    'created_at' => now(), // Aujourd'hui
                    'updated_at' => now()
                ]);
            }

            // Mettre à jour le package avec les IDs des colis
            $package->update(['colis_ids' => $colisIds]);

            DB::commit();

            $this->info('✅ Données de test créées avec succès pour aujourd\'hui !');
            $this->info('📦 3 colis créés');
            $this->info('📋 1 package créé');
            $this->info('🚚 3 livraisons enregistrées');
            $this->info('💰 Frais de livraison : 1,500 + 1,700 + 1,900 = 5,100 FCFA');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erreur lors de la création des données : ' . $e->getMessage());
        }
    }
}
