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

class TenantBootstrapService
{
	public function bootstrapEntreprise(int $entrepriseId, ?int $userId = null): void
	{
		$userId = $userId ?? auth()->id() ?? 1;

		DB::transaction(function () use ($entrepriseId, $userId) {
			$this->seedTypeColis($entrepriseId, $userId);
			$this->seedTypeEngins($entrepriseId, $userId);
			$this->seedModeLivraisons($userId, $entrepriseId);
			$this->seedPoids($entrepriseId, $userId);
			$this->seedTemps($entrepriseId, $userId);
			$this->seedDelais($entrepriseId, $userId);
			$this->seedConditionnements($userId, $entrepriseId);
			$this->seedEnginsSamples($userId);
			$this->seedZones($entrepriseId, $userId);
			$this->seedTarifs($entrepriseId);
			$this->seedRolePermissions($entrepriseId);
			$this->seedSubscriptionPlan($entrepriseId);
			$this->assignDefaultPlan($userId);
		});
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

	protected function seedEnginsSamples(int $userId): void
	{
		// Créer quelques engins génériques si la table est vide
		if (!DB::table('engins')->exists()) {
			// Récupérer un type engin existant (Moto par défaut)
			$typeMotoId = DB::table('type_engins')->where('libelle', 'Moto')->value('id');
			$typeVoitureId = DB::table('type_engins')->where('libelle', 'Voiture')->value('id');
			$typeCamionnetteId = DB::table('type_engins')->where('libelle', 'Camionnette')->value('id');

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
		SubscriptionPlan::firstOrCreate(
			['entreprise_id' => $entrepriseId, 'slug' => 'starter'],
			[
				'name' => 'Starter',
				'description' => 'Plan de démarrage',
				'price' => 0,
				'currency' => 'XOF',
				'duration_days' => 30,
				'features' => [
					'whatsapp_notifications' => false,
					'firebase_notifications' => true,
				],
				'is_active' => true,
				'sort_order' => 0,
			]
		);
	}

	protected function seedTarifs(int $entrepriseId): void
	{
		try {
			// Augmenter la limite de mémoire pour la génération des tarifs
			ini_set('memory_limit', '512M');

			// Exécuter le seeder pour générer les tarifs de l'entreprise
			Artisan::call('db:seed', [
				'--class' => 'EntrepriseTarifSeeder',
				'--force' => true
			]);

			\Log::info('Tarifs générés pour l\'entreprise', [
				'entreprise_id' => $entrepriseId
			]);
		} catch (\Exception $e) {
			\Log::error('Erreur lors de la génération des tarifs', [
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

				\Log::info('Plan gratuit assigné par défaut', [
					'user_id' => $userId,
					'plan_id' => $freePlan->id,
					'plan_name' => $freePlan->name
				]);
			}
		} catch (\Exception $e) {
			\Log::error('Erreur lors de l\'assignation du plan par défaut', [
				'user_id' => $userId,
				'error' => $e->getMessage()
			]);
		}
	}
}
