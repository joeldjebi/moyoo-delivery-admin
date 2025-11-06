<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Marchand;
use App\Models\Boutique;
use App\Models\Livreur;
use App\Models\Colis;
use App\Models\Livraison;
use App\Models\Ramassage;
use App\Models\PlanificationRamassage;
use App\Models\RamassageColis;
use App\Services\TenantBootstrapService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateTestScenario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-scenario {--email= : Email pour le compte utilisateur}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un scénario complet de test : compte, marchand, boutiques, livreur, colis, livraisons et ramassages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Démarrage de la création du scénario de test...');
        $this->newLine();

        try {
            // 1. Créer un compte utilisateur
            $this->info('📝 Étape 1: Création du compte utilisateur...');
            $user = $this->createUser();
            $this->info("   ✅ Utilisateur créé: {$user->email} (ID: {$user->id})");

            // 2. Créer l'entreprise et faire le bootstrap
            $this->info('🏢 Étape 2: Création de l\'entreprise et bootstrap...');
            $entreprise = $this->createEntreprise($user);
            $this->info("   ✅ Entreprise créée: {$entreprise->name} (ID: {$entreprise->id})");

            // Bootstrap tenant
            app(TenantBootstrapService::class)->bootstrapEntreprise($entreprise->id, $user->id);
            $this->info('   ✅ Bootstrap tenant effectué');

            // 3. Créer un marchand
            $this->info('👤 Étape 3: Création du marchand...');
            $marchand = $this->createMarchand($entreprise, $user);
            $this->info("   ✅ Marchand créé: {$marchand->full_name} (ID: {$marchand->id})");

            // 4. Créer 2 boutiques pour le marchand
            $this->info('🏪 Étape 4: Création de 2 boutiques...');
            $boutique1 = $this->createBoutique($entreprise, $marchand, $user, 1);
            $this->info("   ✅ Boutique 1 créée: {$boutique1->libelle} (ID: {$boutique1->id})");
            $boutique2 = $this->createBoutique($entreprise, $marchand, $user, 2);
            $this->info("   ✅ Boutique 2 créée: {$boutique2->libelle} (ID: {$boutique2->id})");

            // 5. Créer un livreur
            $this->info('🚴 Étape 5: Création du livreur...');
            $livreur = $this->createLivreur($entreprise, $user);
            $this->info("   ✅ Livreur créé: {$livreur->first_name} {$livreur->last_name} (ID: {$livreur->id})");

            // 6. Créer des colis
            $this->info('📦 Étape 6: Création de 5 colis...');
            $colisList = [];
            for ($i = 1; $i <= 5; $i++) {
                $colis = $this->createColis($entreprise, $marchand, $boutique1, $livreur, $user, $i);
                $colisList[] = $colis;
                $this->info("   ✅ Colis {$i} créé: {$colis->code} (ID: {$colis->id})");
            }

            // 7. Effectuer des livraisons
            $this->info('🚚 Étape 7: Création de livraisons...');
            $livraisons = [];
            foreach ($colisList as $index => $colis) {
                $livraison = $this->createLivraison($colis, $marchand, $boutique1, $user);
                $livraisons[] = $livraison;
                $livraisonNum = $index + 1;
                $this->info("   ✅ Livraison {$livraisonNum} créée: {$livraison->numero_de_livraison} (ID: {$livraison->id})");
            }

            // 8. Créer des ramassages
            $this->info('📥 Étape 8: Création de 2 ramassages...');
            $ramassage1 = $this->createRamassage($entreprise, $marchand, $boutique1, $livreur, $user);
            $this->info("   ✅ Ramassage 1 créé: {$ramassage1->code_ramassage} (ID: {$ramassage1->id})");
            $ramassage2 = $this->createRamassage($entreprise, $marchand, $boutique2, $livreur, $user);
            $this->info("   ✅ Ramassage 2 créé: {$ramassage2->code_ramassage} (ID: {$ramassage2->id})");

            // 9. Planifier des ramassages
            $this->info('📅 Étape 9: Planification des ramassages...');
            $planification1 = $this->createPlanificationRamassage($ramassage1, $livreur, $entreprise);
            $this->info("   ✅ Planification 1 créée (ID: {$planification1->id})");
            $planification2 = $this->createPlanificationRamassage($ramassage2, $livreur, $entreprise);
            $this->info("   ✅ Planification 2 créée (ID: {$planification2->id})");

            // 10. Effectuer des ramassages
            $this->info('✅ Étape 10: Effectuation des ramassages...');
            $this->effectuerRamassage($ramassage1, $livreur);
            $this->info("   ✅ Ramassage 1 effectué");
            $this->effectuerRamassage($ramassage2, $livreur);
            $this->info("   ✅ Ramassage 2 effectué");

            $this->newLine();
            $this->info('🎉 Scénario de test créé avec succès !');
            $this->newLine();
            $this->table(
                ['Type', 'ID', 'Détails'],
                [
                    ['Utilisateur', $user->id, $user->email],
                    ['Entreprise', $entreprise->id, $entreprise->name],
                    ['Marchand', $marchand->id, $marchand->full_name],
                    ['Boutique 1', $boutique1->id, $boutique1->libelle],
                    ['Boutique 2', $boutique2->id, $boutique2->libelle],
                    ['Livreur', $livreur->id, $livreur->first_name . ' ' . $livreur->last_name],
                    ['Colis', count($colisList), count($colisList) . ' colis créés'],
                    ['Livraisons', count($livraisons), count($livraisons) . ' livraisons créées'],
                    ['Ramassages', 2, '2 ramassages créés et effectués'],
                ]
            );

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création du scénario: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            Log::error('Erreur création scénario test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    protected function createUser()
    {
        $email = $this->option('email') ?? 'test_' . time() . '@example.com';
        $mobile = '07' . rand(10000000, 99999999);

        // Vérifier si l'utilisateur existe déjà
        $existingUser = User::where('email', $email)->orWhere('mobile', $mobile)->first();
        if ($existingUser) {
            $email = 'test_' . time() . '_' . rand(1000, 9999) . '@example.com';
            $mobile = '07' . rand(10000000, 99999999);
        }

        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'mobile' => $mobile,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'user_type' => 'entreprise_admin',
            'role' => 'admin',
        ]);

        return $user;
    }

    protected function createEntreprise($user)
    {
        // Récupérer une commune
        $commune = DB::table('communes')->first();
        if (!$commune) {
            throw new \Exception('Aucune commune trouvée. Exécutez d\'abord les migrations.');
        }

        $entreprise = Entreprise::create([
            'name' => 'Entreprise Test ' . time(),
            'mobile' => $user->mobile . '-ENT',
            'email' => $user->email,
            'adresse' => 'Adresse Test',
            'commune_id' => $commune->id,
            'statut' => 1,
            'created_by' => $user->id,
        ]);

        $user->update(['entreprise_id' => $entreprise->id]);

        return $entreprise;
    }

    protected function createMarchand($entreprise, $user)
    {
        $commune = DB::table('communes')->first();

        $marchand = Marchand::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'mobile' => '07' . rand(10000000, 99999999),
            'email' => 'marchand_' . time() . '@example.com',
            'adresse' => 'Adresse Marchand',
            'commune_id' => $commune->id,
            'status' => 'active',
            'entreprise_id' => $entreprise->id,
            'created_by' => $user->id,
        ]);

        return $marchand;
    }

    protected function createBoutique($entreprise, $marchand, $user, $numero)
    {
        $boutique = Boutique::create([
            'libelle' => 'Boutique ' . $numero . ' - ' . $marchand->full_name,
            'mobile' => '07' . rand(10000000, 99999999),
            'adresse' => 'Adresse Boutique ' . $numero,
            'adresse_gps' => '5.3' . rand(100000, 999999) . ', -4.0' . rand(100000, 999999),
            'marchand_id' => $marchand->id,
            'entreprise_id' => $entreprise->id,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        return $boutique;
    }

    protected function createLivreur($entreprise, $user)
    {
        // Récupérer un engin - créer un si nécessaire
        $engin = DB::table('engins')->first();
        if (!$engin) {
            // Créer un type d'engin et un engin
            $typeEngin = DB::table('type_engins')
                ->where('entreprise_id', $entreprise->id)
                ->where('libelle', 'Moto')
                ->first();

            if (!$typeEngin) {
                $typeEnginId = DB::table('type_engins')->insertGetId([
                    'libelle' => 'Moto',
                    'entreprise_id' => $entreprise->id,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $typeEnginId = $typeEngin->id;
            }

            $enginId = DB::table('engins')->insertGetId([
                'type_engin_id' => $typeEnginId,
                'libelle' => 'Moto Test',
                'matricule' => 'MAT-TEST-' . time(),
                'marque' => 'Honda',
                'modele' => 'CG125',
                'couleur' => 'Noir',
                'immatriculation' => 'AB-TEST',
                'etat' => 'bon',
                'status' => 'actif',
                'created_by' => (string) $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $engin = (object) ['id' => $enginId];
        }

        // Récupérer une commune (zone_activite_id référence communes.id, pas zone_activites.id)
        $commune = DB::table('communes')->first();
        if (!$commune) {
            throw new \Exception('Aucune commune trouvée. Exécutez d\'abord les migrations.');
        }

        $livreur = Livreur::create([
            'first_name' => 'Kouassi',
            'last_name' => 'Traoré',
            'mobile' => '07' . rand(10000000, 99999999),
            'email' => 'livreur_' . time() . '@example.com',
            'password' => Hash::make('password'),
            'adresse' => 'Adresse Livreur',
            'engin_id' => is_object($engin) ? $engin->id : $engin,
            'zone_activite_id' => $commune->id, // zone_activite_id référence communes.id
            'status' => 'actif',
            'entreprise_id' => $entreprise->id,
            'created_by' => $user->id,
        ]);

        // Attacher des communes au livreur
        $communes = DB::table('communes')->limit(3)->pluck('id');
        $livreur->communes()->attach($communes);

        return $livreur;
    }

    protected function createColis($entreprise, $marchand, $boutique, $livreur, $user, $numero)
    {
        // Récupérer les données nécessaires
        $commune = DB::table('communes')->first();
        $zone = DB::table('zones')->where('entreprise_id', $entreprise->id)->first();
        $typeColis = DB::table('type_colis')->where('entreprise_id', $entreprise->id)->first();
        $poids = DB::table('poids')->where('entreprise_id', $entreprise->id)->first();
        $modeLivraison = DB::table('mode_livraisons')->where('entreprise_id', $entreprise->id)->first();
        $delai = DB::table('delais')->where('entreprise_id', $entreprise->id)->first();
        $temp = DB::table('temps')->where('entreprise_id', $entreprise->id)->first();
        $conditionnement = DB::table('conditionnement_colis')->where('entreprise_id', $entreprise->id)->first();

        if (!$zone) {
            // Créer une zone si elle n'existe pas
            $zoneId = DB::table('zones')->insertGetId([
                'nom' => 'Zone Test',
                'entreprise_id' => $entreprise->id,
                'actif' => true,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $zoneId = $zone->id;
        }

        $colis = Colis::create([
            'entreprise_id' => $entreprise->id,
            'status' => Colis::STATUS_EN_ATTENTE,
            'nom_client' => 'Client Test ' . $numero,
            'telephone_client' => '07' . rand(10000000, 99999999),
            'adresse_client' => 'Adresse Client ' . $numero,
            'montant_a_encaisse' => rand(1000, 50000),
            'prix_de_vente' => rand(500, 10000),
            'zone_id' => $zoneId,
            'commune_id' => $commune->id,
            'livreur_id' => $livreur->id,
            'poids_id' => $poids ? $poids->id : null,
            'mode_livraison_id' => $modeLivraison ? $modeLivraison->id : null,
            'temp_id' => $temp ? $temp->id : null,
            'created_by' => $user->id,
        ]);

        return $colis;
    }

    protected function createLivraison($colis, $marchand, $boutique, $user)
    {
        $livraison = Livraison::create([
            'uuid' => Str::uuid(),
            'numero_de_livraison' => 'LV-' . strtoupper(Str::random(8)),
            'colis_id' => $colis->id,
            'marchand_id' => $marchand->id,
            'boutique_id' => $boutique->id,
            'adresse_de_livraison' => $colis->adresse_client,
            'status' => Livraison::STATUS_EN_ATTENTE,
            'created_by' => (string) $user->id,
        ]);

        // Mettre à jour le statut du colis
        $colis->update(['status' => Colis::STATUS_EN_COURS]);

        return $livraison;
    }

    protected function createRamassage($entreprise, $marchand, $boutique, $livreur, $user)
    {
        $codeRamassage = 'RAM-' . strtoupper(Str::random(8));

        $ramassage = Ramassage::create([
            'code_ramassage' => $codeRamassage,
            'entreprise_id' => $entreprise->id,
            'marchand_id' => $marchand->id,
            'boutique_id' => $boutique->id,
            'date_demande' => now(),
            'statut' => 'demande',
            'adresse_ramassage' => $boutique->adresse,
            'contact_ramassage' => $marchand->full_name,
            'telephone_contact' => $marchand->mobile,
            'nombre_colis_estime' => 3,
            'livreur_id' => $livreur->id,
            'montant_total' => rand(10000, 50000),
        ]);

        return $ramassage;
    }

    protected function createPlanificationRamassage($ramassage, $livreur, $entreprise)
    {
        $planification = PlanificationRamassage::create([
            'ramassage_id' => $ramassage->id,
            'livreur_id' => $livreur->id,
            'entreprise_id' => $entreprise->id,
            'date_planifiee' => now()->addDay(),
            'heure_debut' => '08:00:00',
            'heure_fin' => '12:00:00',
            'zone_ramassage' => 'Zone de ramassage',
            'ordre_visite' => 1,
            'statut_planification' => 'planifie',
            'notes_planification' => 'Planification automatique pour test',
        ]);

        // Mettre à jour le statut du ramassage
        $ramassage->update([
            'statut' => 'planifie',
            'date_planifiee' => now()->addDay(),
        ]);

        return $planification;
    }

    protected function effectuerRamassage($ramassage, $livreur)
    {
        $ramassage->update([
            'statut' => 'termine',
            'date_effectuee' => now(),
            'date_debut_ramassage' => now()->subHour(),
            'date_fin_ramassage' => now(),
            'nombre_colis_reel' => $ramassage->nombre_colis_estime,
            'difference_colis' => 0,
            'type_difference' => null,
            'notes_ramassage' => 'Ramassage effectué avec succès',
        ]);

        // Mettre à jour la planification
        $planification = PlanificationRamassage::where('ramassage_id', $ramassage->id)->first();
        if ($planification) {
            $planification->update([
                'statut_planification' => 'termine',
            ]);
        }
    }
}