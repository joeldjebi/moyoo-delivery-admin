<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ServiceAccountFirebaseService;
use App\Models\Colis;
use App\Models\Livreur;

class TestColisNotification extends Command
{
    protected $signature = 'firebase:test-colis {--token=} {--colis-id=}';
    protected $description = 'Tester les notifications de colis (création et mise à jour)';

    public function handle()
    {
        $token = $this->option('token');
        $colisId = $this->option('colis-id');

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

            // Créer un colis de test
            $colis = new Colis();
            $colis->id = $colisId ?: 999;
            $colis->code = 'TEST-' . time();
            $colis->nom_client = 'Test Client';
            $colis->telephone_client = '+225 07 12 34 56 78';
            $colis->adresse_client = '123 Rue Test, Test City';
            $colis->montant_a_encaisse = 5000;
            $colis->statut = 0;
            $colis->created_at = now();
            $colis->updated_at = now();

            // Test 1: Notification de création de colis
            $this->info('🧪 Test 1: Notification de création de colis');
            $result1 = $firebaseService->sendColisCreatedNotification($livreur, $colis);
            if ($result1['success']) {
                $this->info('✅ Test 1 réussi - Notification de création');
            } else {
                $this->error('❌ Test 1 échoué: ' . $result1['message']);
            }

            // Test 2: Notification de mise à jour de colis
            $this->info('🧪 Test 2: Notification de mise à jour de colis');
            $changes = [
                'livreur_id' => ['old' => 1, 'new' => 2],
                'engin_id' => ['old' => 1, 'new' => 3],
                'status' => ['old' => 0, 'new' => 1]
            ];
            $result2 = $firebaseService->sendColisUpdatedNotification($livreur, $colis, $changes);
            if ($result2['success']) {
                $this->info('✅ Test 2 réussi - Notification de mise à jour');
            } else {
                $this->error('❌ Test 2 échoué: ' . $result2['message']);
            }

            // Test 3: Notification d'annulation de colis
            $this->info('🧪 Test 3: Notification d\'annulation de colis');
            $result3 = $firebaseService->sendColisCancelledNotification($livreur, $colis, 'Client non disponible');
            if ($result3['success']) {
                $this->info('✅ Test 3 réussi - Notification d\'annulation');
            } else {
                $this->error('❌ Test 3 échoué: ' . $result3['message']);
            }

            // Test 4: Notification avec colis existant (si ID fourni)
            if ($colisId) {
                $this->info('🧪 Test 4: Notification avec colis existant');
                $existingColis = Colis::find($colisId);
                if ($existingColis) {
                    $result4 = $firebaseService->sendColisCreatedNotification($livreur, $existingColis);
                    if ($result4['success']) {
                        $this->info('✅ Test 4 réussi - Notification avec colis existant');
                    } else {
                        $this->error('❌ Test 4 échoué: ' . $result4['message']);
                    }
                } else {
                    $this->warn('⚠️ Colis avec ID ' . $colisId . ' non trouvé');
                }
            }

            $this->info('🎉 Tous les tests de colis terminés !');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
