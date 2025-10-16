<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ServiceAccountFirebaseService;
use App\Models\Ramassage;
use App\Models\Livreur;

class TestRamassageNotification extends Command
{
    protected $signature = 'firebase:test-ramassage {--token=} {--ramassage-id=}';
    protected $description = 'Tester les notifications de ramassage avec gestion des données de colis';

    public function handle()
    {
        $token = $this->option('token');
        $ramassageId = $this->option('ramassage-id');

        if (!$token) {
            $this->error('Veuillez fournir un token FCM avec --token=YOUR_TOKEN');
            return 1;
        }

        try {
            $firebaseService = app(ServiceAccountFirebaseService::class);

            // Créer un livreur fictif avec le token
            $livreur = new Livreur();
            $livreur->id = 999;
            $livreur->fcm_token = $token;
            $livreur->nom = 'Test Livreur';

            // Créer un ramassage de test avec différents types de données
            $ramassage = new Ramassage();
            $ramassage->id = $ramassageId ?: 999;
            $ramassage->code_ramassage = 'TEST-' . time();
            $ramassage->marchand_name = 'Test Marchand';
            $ramassage->adresse_ramassage = '123 Rue Test, Test City';

            // Test avec des données de colis sous forme de tableau
            $this->info('🧪 Test 1: Données de colis sous forme de tableau');
            $ramassage->colis_data = [
                ['id' => 1, 'code' => 'COLIS-001'],
                ['id' => 2, 'code' => 'COLIS-002'],
                ['id' => 3, 'code' => 'COLIS-003']
            ];

            $result1 = $firebaseService->sendNewRamassageNotification($livreur, $ramassage);
            if ($result1['success']) {
                $this->info('✅ Test 1 réussi - Tableau de colis');
            } else {
                $this->error('❌ Test 1 échoué: ' . $result1['message']);
            }

            // Test avec des données de colis sous forme de chaîne JSON
            $this->info('🧪 Test 2: Données de colis sous forme de chaîne JSON');
            $ramassage->colis_data = json_encode([
                ['id' => 4, 'code' => 'COLIS-004'],
                ['id' => 5, 'code' => 'COLIS-005']
            ]);

            $result2 = $firebaseService->sendNewRamassageNotification($livreur, $ramassage);
            if ($result2['success']) {
                $this->info('✅ Test 2 réussi - Chaîne JSON de colis');
            } else {
                $this->error('❌ Test 2 échoué: ' . $result2['message']);
            }

            // Test avec des données de colis vides
            $this->info('🧪 Test 3: Données de colis vides');
            $ramassage->colis_data = [];

            $result3 = $firebaseService->sendNewRamassageNotification($livreur, $ramassage);
            if ($result3['success']) {
                $this->info('✅ Test 3 réussi - Données vides');
            } else {
                $this->error('❌ Test 3 échoué: ' . $result3['message']);
            }

            // Test avec des données de colis null
            $this->info('🧪 Test 4: Données de colis null');
            $ramassage->colis_data = null;

            $result4 = $firebaseService->sendNewRamassageNotification($livreur, $ramassage);
            if ($result4['success']) {
                $this->info('✅ Test 4 réussi - Données null');
            } else {
                $this->error('❌ Test 4 échoué: ' . $result4['message']);
            }

            // Test avec des données de colis invalides
            $this->info('🧪 Test 5: Données de colis invalides');
            $ramassage->colis_data = 'données invalides';

            $result5 = $firebaseService->sendNewRamassageNotification($livreur, $ramassage);
            if ($result5['success']) {
                $this->info('✅ Test 5 réussi - Données invalides gérées');
            } else {
                $this->error('❌ Test 5 échoué: ' . $result5['message']);
            }

            $this->info('🎉 Tous les tests de ramassage terminés !');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
