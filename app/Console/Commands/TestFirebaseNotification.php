<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ServiceAccountFirebaseService;
use App\Models\Livreur;
use App\Models\Marchand;

class TestFirebaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test {--token=} {--type=livreur} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'envoi de notifications Firebase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = $this->option('token');
        $type = $this->option('type');
        $id = $this->option('id');

        if (!$token) {
            $this->error('Veuillez fournir un token FCM avec --token=YOUR_TOKEN');
            return 1;
        }

        try {
            $firebaseService = app(ServiceAccountFirebaseService::class);

            $this->info('Test de notification Firebase...');
            $this->info("Token: {$token}");
            $this->info("Type: {$type}");

            // Test de notification simple
            $result = $firebaseService->sendCustomNotification(
                $token,
                'Test Notification',
                'Ceci est un test de notification Firebase depuis votre application Laravel',
                [
                    'type' => 'test',
                    'timestamp' => now()->toISOString()
                ]
            );

            if ($result['success']) {
                $this->info('✅ Notification envoyée avec succès !');
                $this->line('Réponse: ' . json_encode($result['response'], JSON_PRETTY_PRINT));
            } else {
                $this->error('❌ Échec de l\'envoi de la notification');
                $this->error('Erreur: ' . $result['message']);
            }

            // Test avec un utilisateur spécifique si ID fourni
            if ($id) {
                $this->info("\n--- Test avec utilisateur spécifique ---");

                if ($type === 'livreur') {
                    $livreur = Livreur::find($id);
                    if ($livreur) {
                        $this->info("Livreur trouvé: {$livreur->nom_complet}");

                        // Mettre à jour le token FCM du livreur
                        $livreur->update(['fcm_token' => $token]);
                        $this->info('Token FCM mis à jour pour le livreur');

                        // Test de notification de nouveau colis
                        $colis = (object) [
                            'id' => 1,
                            'code' => 'TEST001',
                            'nom_client' => 'Client Test',
                            'adresse_client' => 'Adresse Test',
                            'montant_a_encaisse' => 1000
                        ];

                        $result = $firebaseService->sendNewColisNotification($livreur, $colis);

                        if ($result['success']) {
                            $this->info('✅ Notification nouveau colis envoyée !');
                        } else {
                            $this->error('❌ Échec notification nouveau colis: ' . $result['message']);
                        }
                    } else {
                        $this->error("Livreur avec ID {$id} non trouvé");
                    }
                } elseif ($type === 'marchand') {
                    $marchand = Marchand::find($id);
                    if ($marchand) {
                        $this->info("Marchand trouvé: {$marchand->nom_complet}");

                        // Mettre à jour le token FCM du marchand
                        $marchand->update(['fcm_token' => $token]);
                        $this->info('Token FCM mis à jour pour le marchand');

                        // Test de notification de colis livré
                        $colis = (object) [
                            'id' => 1,
                            'code' => 'TEST001',
                            'nom_client' => 'Client Test'
                        ];

                        $result = $firebaseService->sendColisDeliveredNotification($marchand, $colis);

                        if ($result['success']) {
                            $this->info('✅ Notification colis livré envoyée !');
                        } else {
                            $this->error('❌ Échec notification colis livré: ' . $result['message']);
                        }
                    } else {
                        $this->error("Marchand avec ID {$id} non trouvé");
                    }
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Erreur lors du test: ' . $e->getMessage());
            return 1;
        }
    }
}
