<?php

namespace App\Services;

use App\Models\Type_colis;
use App\Models\Type_engin;
use App\Models\Zone_activite;
use App\Models\RolePermission;
use App\Models\SubscriptionPlan;
use App\Models\Commune_zone;
use App\Models\Zone_activite as ZoneActiviteModel; // avoid duplicate import name
use App\Models\Temp;
use App\Models\Delais;
use App\Models\Conditionnement_colis;
use App\Models\Engin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TenantBootstrapService
{
	public function bootstrapEntreprise(int $entrepriseId, ?int $userId = null): void
	{
		$userId = $userId ?? auth()->id() ?? 1;

		Log::info('Bootstrap entreprise démarré', [
			'entreprise_id' => $entrepriseId,
			'user_id' => $userId
		]);

		// Exécuter chaque étape individuellement avec gestion d'erreur
		// pour s'assurer que les erreurs d'une étape n'empêchent pas les autres
		$steps = [
			'TypeColis' => fn() => $this->seedTypeColis($entrepriseId, $userId),
			'TypeEngins' => fn() => $this->seedTypeEngins($entrepriseId, $userId),
			'ModeLivraisons' => fn() => $this->seedModeLivraisons($userId, $entrepriseId),
			'Poids' => fn() => $this->seedPoids($entrepriseId, $userId),
			'Temps' => fn() => $this->seedTemps($entrepriseId, $userId),
			'Delais' => fn() => $this->seedDelais($entrepriseId, $userId),
			'Conditionnements' => fn() => $this->seedConditionnements($userId, $entrepriseId),
			'EnginsSamples' => fn() => $this->seedEnginsSamples($entrepriseId, $userId),
			'Zones' => fn() => $this->seedZones($entrepriseId, $userId),
			'RolePermissions' => fn() => $this->seedRolePermissions($entrepriseId),
			'SubscriptionPlan' => fn() => $this->seedSubscriptionPlan($entrepriseId),
			'AssignDefaultPlan' => fn() => $this->assignDefaultPlan($userId),
		];

		$failedSteps = [];
		foreach ($steps as $stepName => $stepCallback) {
			try {
				Log::info("Bootstrap étape: {$stepName}", ['entreprise_id' => $entrepriseId]);
				$stepCallback();
				Log::info("Bootstrap étape réussie: {$stepName}", ['entreprise_id' => $entrepriseId]);
			} catch (\Exception $e) {
				Log::error("Bootstrap étape échouée: {$stepName}", [
					'entreprise_id' => $entrepriseId,
					'error' => $e->getMessage(),
					'trace' => $e->getTraceAsString()
				]);
				$failedSteps[] = $stepName;
				// Continuer avec les autres étapes même si celle-ci échoue
			}
		}

		// Génération des tarifs en dernier (peut être long et peut échouer sans bloquer)
		try {
			Log::info('Bootstrap étape: Tarifs', ['entreprise_id' => $entrepriseId]);
			$this->seedTarifs($entrepriseId);
			Log::info('Bootstrap étape réussie: Tarifs', ['entreprise_id' => $entrepriseId]);
		} catch (\Exception $e) {
			Log::error('Bootstrap étape échouée: Tarifs', [
				'entreprise_id' => $entrepriseId,
				'error' => $e->getMessage()
			]);
			$failedSteps[] = 'Tarifs';
		}

		// Vérification post-bootstrap
		$verification = $this->verifyBootstrap($entrepriseId, $userId);

		if (!empty($failedSteps) || !$verification['success']) {
			Log::warning('Bootstrap terminé avec des avertissements', [
				'entreprise_id' => $entrepriseId,
				'failed_steps' => $failedSteps,
				'verification' => $verification
			]);

			// Tenter de réparer les données manquantes
			if (!$verification['success']) {
				$this->repairMissingData($entrepriseId, $userId, $verification['missing']);

				// Vérifier à nouveau après réparation
				$verificationAfter = $this->verifyBootstrap($entrepriseId, $userId);
				if ($verificationAfter['success']) {
					Log::info('Bootstrap réparé avec succès après réparation', [
						'entreprise_id' => $entrepriseId
					]);
				} else {
					Log::error('Bootstrap incomplet même après réparation', [
						'entreprise_id' => $entrepriseId,
						'still_missing' => $verificationAfter['missing']
					]);
				}
			}
		} else {
			Log::info('Bootstrap entreprise terminé avec succès', [
				'entreprise_id' => $entrepriseId,
				'user_id' => $userId
			]);
		}
	}

	protected function seedTypeColis(int $entrepriseId, int $userId): void
	{
		$defaults = [
			['libelle' => 'Document'],
			['libelle' => 'Colis réfrigéré'],
		];

		foreach ($defaults as $d) {
			Type_colis::firstOrCreate(
				['entreprise_id' => $entrepriseId, 'libelle' => $d['libelle']],
				['created_by' => $userId]
			);
		}
	}

	protected function seedTypeEngins(int $entrepriseId, int $userId): void
	{
		$defaults = [
			['libelle' => 'Moto'],
			['libelle' => 'Voiture'],
			['libelle' => 'Camionnette'],
		];

		foreach ($defaults as $d) {
			Type_engin::firstOrCreate(
				['entreprise_id' => $entrepriseId, 'libelle' => $d['libelle']],
				['created_by' => $userId]
			);
		}
	}

	protected function seedModeLivraisons(int $userId, int $entrepriseId): void
	{
		$items = ['Standard', 'Express'];
		foreach ($items as $libelle) {
			$exists = DB::table('mode_livraisons')
				->where('libelle', $libelle)
				->where('entreprise_id', $entrepriseId)
				->exists();
			if (!$exists) {
				DB::table('mode_livraisons')->insert([
					'libelle' => $libelle,
					'entreprise_id' => $entrepriseId,
					'created_by' => $userId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	protected function seedPoids(int $entrepriseId, int $userId): void
	{
		$rows = [
			['libelle' => '0 - 1 kg'],
			['libelle' => '1 - 5 kg'],
			['libelle' => '5 - 10 kg'],
			['libelle' => '10 - 20 kg'],
			['libelle' => '20 - 50 kg'],
			['libelle' => '50 - 100 kg'],
			['libelle' => '100 - 200+ kg'],
		];
		foreach ($rows as $r) {
			$exists = DB::table('poids')
				->where('libelle', $r['libelle'])
				->where('entreprise_id', $entrepriseId)
				->exists();
			if (!$exists) {
				DB::table('poids')->insert([
					'libelle' => $r['libelle'],
					'created_by' => (string) $userId,
					'entreprise_id' => $entrepriseId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	protected function seedTemps(int $entrepriseId, int $userId): void
	{
		$rows = [
            ['libelle' => 'Nuit', 'description' => 'Créneau de la nuit', 'heure_debut' => '20:00:00', 'heure_fin' => '08:00:00'],
            ['libelle' => 'Journée', 'description' => 'Créneau de la journée', 'heure_debut' => '08:00:00', 'heure_fin' => '20:00:00'],
		];
		foreach ($rows as $r) {
			$exists = DB::table('temps')
				->where('libelle', $r['libelle'])
				->where('entreprise_id', $entrepriseId)
				->exists();
			if (!$exists) {
				DB::table('temps')->insert([
					'entreprise_id' => $entrepriseId,
					'libelle' => $r['libelle'],
					'description' => $r['description'],
					'heure_debut' => $r['heure_debut'],
					'heure_fin' => $r['heure_fin'],
					'is_weekend' => 0,
					'is_holiday' => 0,
					'is_active' => 1,
					'created_by' => $userId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	protected function seedDelais(int $entrepriseId, int $userId): void
	{
		$rows = [
			['libelle' => 'J+0 - Même jour'],
			['libelle' => 'J+1 - 24h'],
		];
		foreach ($rows as $r) {
			$exists = DB::table('delais')
				->where('libelle', $r['libelle'])
				->where('entreprise_id', $entrepriseId)
				->exists();
			if (!$exists) {
				DB::table('delais')->insert([
					'entreprise_id' => $entrepriseId,
					'libelle' => $r['libelle'],
					'created_by' => (string) $userId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	protected function seedConditionnements(int $userId, int $entrepriseId): void
	{
		$items = ['Carton', 'Palette', 'Enveloppe', 'Boîte', 'Sac', 'Autre'];
		foreach ($items as $libelle) {
			$exists = DB::table('conditionnement_colis')
				->where('libelle', $libelle)
				->where('entreprise_id', $entrepriseId)
				->exists();
			if (!$exists) {
				DB::table('conditionnement_colis')->insert([
					'libelle' => $libelle,
					'entreprise_id' => $entrepriseId,
					'created_by' => $userId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	protected function seedEnginsSamples(int $entrepriseId, int $userId): void
	{
		// Créer quelques engins génériques pour l'entreprise si aucun n'existe
		$hasEngins = DB::table('engins')
			->whereIn('type_engin_id', function($query) use ($entrepriseId) {
				$query->select('id')
					->from('type_engins')
					->where('entreprise_id', $entrepriseId);
			})
			->exists();

		if (!$hasEngins) {
			// Récupérer les types d'engins de l'entreprise
			$typeMotoId = DB::table('type_engins')
				->where('entreprise_id', $entrepriseId)
				->where('libelle', 'Moto')
				->value('id');
			$typeVoitureId = DB::table('type_engins')
				->where('entreprise_id', $entrepriseId)
				->where('libelle', 'Voiture')
				->value('id');
			$typeCamionnetteId = DB::table('type_engins')
				->where('entreprise_id', $entrepriseId)
				->where('libelle', 'Camionnette')
				->value('id');

			$rows = [];
			if ($typeMotoId) {
				$rows[] = [
					'type_engin_id' => $typeMotoId,
					'libelle' => 'Moto A',
					'matricule' => 'MAT-001',
					'marque' => 'Honda',
					'modele' => 'CG125',
					'couleur' => 'Noir',
					'immatriculation' => 'AB-1234',
					'etat' => 'bon',
					'status' => 'actif',
					'created_by' => (string) $userId,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
			if ($typeVoitureId) {
				$rows[] = [
					'type_engin_id' => $typeVoitureId,
					'libelle' => 'Voiture A',
					'matricule' => 'MAT-002',
					'marque' => 'Toyota',
					'modele' => 'Yaris',
					'couleur' => 'Blanc',
					'immatriculation' => 'BC-5678',
					'etat' => 'bon',
					'status' => 'actif',
					'created_by' => (string) $userId,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
			if ($typeCamionnetteId) {
				$rows[] = [
					'type_engin_id' => $typeCamionnetteId,
					'libelle' => 'Camionnette A',
					'matricule' => 'MAT-003',
					'marque' => 'Renault',
					'modele' => 'Kangoo',
					'couleur' => 'Blanc',
					'immatriculation' => 'CD-9012',
					'etat' => 'bon',
					'status' => 'actif',
					'created_by' => (string) $userId,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}

			if (!empty($rows)) {
				DB::table('engins')->insert($rows);
			}
		}
	}

	protected function seedZones(int $entrepriseId, int $userId): void
	{
		// Créer les zones d'activités à partir des communes (libelle)
		$communes = DB::table('communes')->select('id', 'libelle')->get();

		foreach ($communes as $commune) {
			Zone_activite::firstOrCreate(
				[
					'entreprise_id' => $entrepriseId,
					'code' => (string) $commune->id
				],
				[
					'libelle' => $commune->libelle,
					'created_by' => $userId
				]
			);
		}
	}

	protected function seedRolePermissions(int $entrepriseId): void
	{
		$map = [
			'admin' => [
				'users.create','users.read','users.update','users.delete',
				'colis.create','colis.read','colis.update','colis.delete',
				'livreurs.create','livreurs.read','livreurs.update','livreurs.delete',
				'marchands.create','marchands.read','marchands.update','marchands.delete',
				'reversements.create','reversements.read','reversements.update',
				'reports.read','settings.read','settings.update'
			],
			'manager' => [
				'colis.create','colis.read','colis.update','colis.delete',
				'livreurs.create','livreurs.read','livreurs.update','livreurs.delete',
				'marchands.create','marchands.read','marchands.update','marchands.delete',
				'reports.read'
			],
			'user' => [
				'colis.create','colis.read','livreurs.read','marchands.read'
			],
		];

		foreach ($map as $role => $permissions) {
			RolePermission::updateOrCreate(
				['role' => $role, 'entreprise_id' => $entrepriseId],
				['permissions' => $permissions]
			);
		}
	}

    protected function seedSubscriptionPlan(int $entrepriseId): void
    {
        try {
            // Aligner sur pricing_plans (plan gratuit par défaut)
            $pricingFree = \App\Models\PricingPlan::where('price', 0)->first();

            if (!$pricingFree) {
                Log::warning('Aucun plan gratuit trouvé dans pricing_plans', [
                    'entreprise_id' => $entrepriseId
                ]);
            }

            $payload = [
                'name' => $pricingFree->name ?? 'Free',
                'slug' => 'free',
                'entreprise_id' => $entrepriseId,
                'description' => $pricingFree->description ?? 'Plan gratuit pour commencer',
                'price' => $pricingFree->price ?? 0,
                'currency' => $pricingFree->currency ?? 'XOF',
                'duration_days' => 30,
                'features' => $pricingFree->features ?? [
                    "Jusqu'à 20 colis par mois",
                    "Jusqu'à 2 livreurs",
                    "Jusqu'à 5 marchands",
                    "Support par email",
                    "Tableau de bord basique",
                    "Rapports mensuels",
                    "Suivi en temps réel"
                ],
                'max_colis_per_month' => 20,
                'max_livreurs' => 2,
                'max_marchands' => 5,
                'whatsapp_notifications' => false,
                'firebase_notifications' => false,
                'api_access' => false,
                'advanced_reports' => false,
                'priority_support' => false,
                'pricing_plan_id' => $pricingFree->id ?? null,
                'is_active' => true,
                'sort_order' => 1,
                'started_at' => now(),
                'expires_at' => now()->addYear(), // Plan gratuit = 1 an
            ];

            $subscriptionPlan = SubscriptionPlan::updateOrCreate(
                ['entreprise_id' => $entrepriseId, 'slug' => 'free'],
                $payload
            );

            Log::info('Subscription plan créé/mis à jour pour l\'entreprise', [
                'entreprise_id' => $entrepriseId,
                'subscription_plan_id' => $subscriptionPlan->id,
                'name' => $subscriptionPlan->name,
                'slug' => $subscriptionPlan->slug
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du subscription plan', [
                'entreprise_id' => $entrepriseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Ne pas faire échouer le bootstrap complet si le subscription plan échoue
        }
    }

	protected function seedTarifs(int $entrepriseId): void
	{
        try {
			// Augmenter la limite de mémoire pour la génération des tarifs
			ini_set('memory_limit', '512M');

			// Exécuter le seeder pour générer les tarifs de l'entreprise
            \Config::set('seed.entreprise_id', $entrepriseId);
            Artisan::call('db:seed', [
				'--class' => 'EntrepriseTarifSeeder',
				'--force' => true
			]);

			Log::info('Tarifs générés pour l\'entreprise', [
				'entreprise_id' => $entrepriseId
			]);
		} catch (\Exception $e) {
			Log::error('Erreur lors de la génération des tarifs', [
				'entreprise_id' => $entrepriseId,
				'error' => $e->getMessage()
			]);
			// Ne pas faire échouer le bootstrap complet si les tarifs échouent
		}
	}

	protected function assignDefaultPlan(int $userId): void
	{
		try {
			// Récupérer le plan gratuit (Plan Démarrage)
			$freePlan = \App\Models\PricingPlan::where('price', 0)->first();

			if ($freePlan) {
				// Créer l'historique d'abonnement
				\App\Models\SubscriptionHistory::create([
					'user_id' => $userId,
					'pricing_plan_id' => $freePlan->id,
					'amount' => $freePlan->price,
					'currency' => $freePlan->currency,
					'period' => $freePlan->period,
					'status' => 'active',
					'starts_at' => now(),
					'expires_at' => now()->addYear(), // Plan gratuit = 1 an
					'is_trial' => false,
					'payment_method' => 'free',
					'transaction_id' => 'FREE_' . $userId . '_' . time()
				]);

				// Mettre à jour l'utilisateur
				\App\Models\User::where('id', $userId)->update([
					'current_pricing_plan_id' => $freePlan->id,
					'subscription_status' => 'active',
					'subscription_expires_at' => now()->addYear()
				]);

				Log::info('Plan gratuit assigné par défaut', [
					'user_id' => $userId,
					'plan_id' => $freePlan->id,
					'plan_name' => $freePlan->name
				]);
			}
		} catch (\Exception $e) {
			Log::error('Erreur lors de l\'assignation du plan par défaut', [
				'user_id' => $userId,
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Vérifier que toutes les données nécessaires ont été créées
	 */
	public function verifyBootstrap(int $entrepriseId, int $userId): array
	{
		$missing = [];
		$checks = [
			'type_colis' => fn() => DB::table('type_colis')->where('entreprise_id', $entrepriseId)->exists(),
			'type_engins' => fn() => DB::table('type_engins')->where('entreprise_id', $entrepriseId)->exists(),
			'mode_livraisons' => fn() => DB::table('mode_livraisons')->where('entreprise_id', $entrepriseId)->exists(),
			'poids' => fn() => DB::table('poids')->where('entreprise_id', $entrepriseId)->exists(),
			'temps' => fn() => DB::table('temps')->where('entreprise_id', $entrepriseId)->exists(),
			'delais' => fn() => DB::table('delais')->where('entreprise_id', $entrepriseId)->exists(),
			'conditionnement_colis' => fn() => DB::table('conditionnement_colis')->where('entreprise_id', $entrepriseId)->exists(),
			'zone_activites' => fn() => DB::table('zone_activites')->where('entreprise_id', $entrepriseId)->exists(),
			'role_permissions' => fn() => \App\Models\RolePermission::where('entreprise_id', $entrepriseId)->exists(),
			'subscription_plans' => fn() => \App\Models\SubscriptionPlan::where('entreprise_id', $entrepriseId)->exists(),
		];

		foreach ($checks as $checkName => $checkCallback) {
			try {
				if (!$checkCallback()) {
					$missing[] = $checkName;
				}
			} catch (\Exception $e) {
				Log::warning("Vérification bootstrap échouée: {$checkName}", [
					'entreprise_id' => $entrepriseId,
					'error' => $e->getMessage()
				]);
				$missing[] = $checkName;
			}
		}

		return [
			'success' => empty($missing),
			'missing' => $missing,
			'total_checks' => count($checks),
			'passed_checks' => count($checks) - count($missing)
		];
	}

	/**
	 * Réparer les données manquantes après le bootstrap
	 */
	public function repairMissingData(int $entrepriseId, int $userId, array $missing): void
	{
		Log::info('Réparation des données manquantes', [
			'entreprise_id' => $entrepriseId,
			'missing' => $missing
		]);

		$repairMethods = [
			'type_colis' => fn() => $this->seedTypeColis($entrepriseId, $userId),
			'type_engins' => fn() => $this->seedTypeEngins($entrepriseId, $userId),
			'mode_livraisons' => fn() => $this->seedModeLivraisons($userId, $entrepriseId),
			'poids' => fn() => $this->seedPoids($entrepriseId, $userId),
			'temps' => fn() => $this->seedTemps($entrepriseId, $userId),
			'delais' => fn() => $this->seedDelais($entrepriseId, $userId),
			'conditionnement_colis' => fn() => $this->seedConditionnements($userId, $entrepriseId),
			'zone_activites' => fn() => $this->seedZones($entrepriseId, $userId),
			'role_permissions' => fn() => $this->seedRolePermissions($entrepriseId),
			'subscription_plans' => fn() => $this->seedSubscriptionPlan($entrepriseId),
		];

		foreach ($missing as $missingItem) {
			if (isset($repairMethods[$missingItem])) {
				try {
					Log::info("Réparation: {$missingItem}", ['entreprise_id' => $entrepriseId]);
					$repairMethods[$missingItem]();
					Log::info("Réparation réussie: {$missingItem}", ['entreprise_id' => $entrepriseId]);
				} catch (\Exception $e) {
					Log::error("Réparation échouée: {$missingItem}", [
						'entreprise_id' => $entrepriseId,
						'error' => $e->getMessage()
					]);
				}
			}
		}
	}
}
