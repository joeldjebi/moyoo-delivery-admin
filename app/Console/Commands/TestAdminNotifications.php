<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Colis;
use App\Models\Ramassage;
use App\Models\Livreur;
use App\Services\ServiceAccountFirebaseService;

class TestAdminNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test des notifications push pour l\'admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Test des notifications admin ===');
        $this->newLine();

        // 1. Vérifier qu'il y a un admin avec un token FCM
        $admin = User::where(function($query) {
                $query->where('user_type', 'admin')
                      ->orWhere('user_type', 'entreprise_user');
            })
            ->whereNotNull('fcm_token')
            ->first();

        if (!$admin) {
            $this->error('❌ Aucun admin trouvé avec un token FCM');
            $this->info('Pour tester, vous devez d\'abord enregistrer un token FCM pour l\'admin via l\'API:');
            $this->info('POST /api/fcm-token');
            $this->info('Body: {"fcm_token": "votre_token_fcm_ici"}');
            $this->newLine();
            return;
        }

        $this->info("✅ Admin trouvé: {$admin->first_name} {$admin->last_name} (ID: {$admin->id})");
        $this->info("   Token FCM: " . substr($admin->fcm_token, 0, 20) . "...");
        $this->newLine();

        // 2. Vérifier qu'il y a des colis et ramassages de test
        $colis = Colis::where('status', 2)->first(); // Colis livré
        $ramassage = Ramassage::where('statut', 'termine')->first();
        $livreur = Livreur::first();

        if (!$colis) {
            $this->error('❌ Aucun colis livré trouvé pour le test');
            return;
        }

        if (!$ramassage) {
            $this->error('❌ Aucun ramassage terminé trouvé pour le test');
            return;
        }

        if (!$livreur) {
            $this->error('❌ Aucun livreur trouvé pour le test');
            return;
        }

        $this->info('✅ Données de test trouvées:');
        $this->info("   - Colis: {$colis->code} (ID: {$colis->id})");
        $this->info("   - Ramassage: {$ramassage->code_ramassage} (ID: {$ramassage->id})");
        $this->info("   - Livreur: {$livreur->first_name} {$livreur->last_name} (ID: {$livreur->id})");
        $this->newLine();

        // 3. Tester le service Firebase
        $this->info('=== Test du service Firebase ===');

        try {
            $firebaseService = new ServiceAccountFirebaseService();
            $this->info('✅ Service Firebase initialisé');
            
            // Test de validation du token
            $isValid = $firebaseService->validateToken($admin->fcm_token);
            $this->info($isValid ? '✅ Token FCM valide' : '❌ Token FCM invalide');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur service Firebase: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('=== Test des notifications ===');

        // 4. Tester la notification de livraison terminée
        $this->info('Test notification livraison terminée...');
        try {
            $result = $firebaseService->sendDeliveryCompletedNotificationToAdmin($colis, $livreur, $admin->fcm_token);
            
            if ($result['success']) {
                $this->info('✅ Notification de livraison envoyée avec succès');
            } else {
                $this->error('❌ Échec notification livraison: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Erreur notification livraison: ' . $e->getMessage());
        }

        // 5. Tester la notification de ramassage terminé
        $this->newLine();
        $this->info('Test notification ramassage terminé...');
        try {
            $result = $firebaseService->sendRamassageCompletedNotificationToAdmin($ramassage, $livreur, $admin->fcm_token);
            
            if ($result['success']) {
                $this->info('✅ Notification de ramassage envoyée avec succès');
            } else {
                $this->error('❌ Échec notification ramassage: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Erreur notification ramassage: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('=== Résumé ===');
        $this->info('Pour recevoir les notifications:');
        $this->info('1. L\'admin doit enregistrer son token FCM via l\'API');
        $this->info('2. Quand un livreur termine une livraison, l\'admin recevra une notification');
        $this->info('3. Quand un livreur termine un ramassage, l\'admin recevra une notification');
        $this->info('4. Les notifications sont envoyées via Firebase Cloud Messaging');
        $this->newLine();

        $this->info('API pour enregistrer le token FCM:');
        $this->info('POST /api/fcm-token');
        $this->info('Headers: Authorization: Bearer {token_admin}');
        $this->info('Body: {"fcm_token": "votre_token_fcm"}');
        $this->newLine();

        $this->info('Test terminé !');
    }
}
