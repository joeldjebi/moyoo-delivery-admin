<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetupAdminFcmToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:admin-fcm-token {token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurer le token FCM pour l\'admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Configuration du token FCM pour l\'admin ===');
        $this->newLine();

        // Trouver le premier admin ou utilisateur entreprise
        $admin = User::where('user_type', 'admin')
            ->orWhere('user_type', 'entreprise_user')
            ->first();

        if (!$admin) {
            $this->error('❌ Aucun utilisateur admin trouvé dans la base de données');
            return;
        }

        $this->info("✅ Admin trouvé: {$admin->first_name} {$admin->last_name} (ID: {$admin->id})");

        // Récupérer le token depuis l'argument ou demander à l'utilisateur
        $token = $this->argument('token');
        
        if (!$token) {
            $token = $this->ask('Entrez le token FCM (ou laissez vide pour un token de test)');
        }

        if (empty($token)) {
            // Simuler un token FCM (en production, ce serait un vrai token)
            $token = 'fake_fcm_token_' . time() . '_' . rand(1000, 9999);
            $this->warn('Token de test généré: ' . $token);
        }

        // Mettre à jour le token FCM
        $admin->update(['fcm_token' => $token]);

        $this->info('✅ Token FCM enregistré: ' . substr($token, 0, 20) . '...');
        $this->newLine();

        $this->info('=== Instructions pour l\'utilisation ===');
        $this->info('1. L\'admin peut maintenant recevoir des notifications push');
        $this->info('2. Quand un livreur termine une livraison, l\'admin recevra une notification');
        $this->info('3. Quand un livreur termine un ramassage, l\'admin recevra une notification');
        $this->info('4. En production, utilisez l\'API pour enregistrer le vrai token FCM:');
        $this->info('   POST /api/fcm-token');
        $this->info('   Headers: Authorization: Bearer {token_admin}');
        $this->info('   Body: {"fcm_token": "votre_vrai_token_fcm"}');
        $this->newLine();

        $this->info('Configuration terminée !');
    }
}
