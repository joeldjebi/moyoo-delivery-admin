<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestFirebaseConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:config-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la configuration Firebase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Test de la configuration Firebase...');
        $this->newLine();

        // Test 1: Vérifier la configuration
        $this->info('1. Vérification de la configuration :');
        $projectId = config('services.firebase.project_id');
        $serverKey = config('services.firebase.server_key');
        $serviceAccountConfigured = !empty(config('services.firebase.service_account_key.client_email'));

        $this->line("   Project ID: " . ($projectId ? "✅ {$projectId}" : "❌ Non configuré"));
        $this->line("   Server Key: " . ($serverKey ? "✅ Configuré" : "❌ Non configuré"));
        $this->line("   Service Account: " . ($serviceAccountConfigured ? "✅ Configuré" : "❌ Non configuré"));
        $this->newLine();

        // Test 2: Tester l'API Key actuelle
        if ($serverKey) {
            $this->info('2. Test de l\'API Key actuelle :');
            $this->testApiKey($serverKey);
        } else {
            $this->warn('2. Pas de Server Key configurée pour le test');
        }

        $this->newLine();

        // Test 3: Instructions
        $this->info('3. Instructions pour corriger :');
        $this->line('   📋 Consultez le guide : FIREBASE_SERVER_KEY_GUIDE.md');
        $this->line('   🔗 Firebase Console : https://console.firebase.google.com/');
        $this->line('   📁 Projet : moyoo-fleet');
        $this->line('   ⚙️ Paramètres → Cloud Messaging → Legacy server key');
        $this->newLine();

        return 0;
    }

    private function testApiKey($apiKey)
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';

            $payload = [
                'to' => 'test_token',
                'data' => ['test' => 'config'],
                'priority' => 'high'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $statusCode = $response->status();
            $result = $response->json();

            if ($statusCode === 200) {
                $this->line("   ✅ API Key valide (Status: {$statusCode})");
            } elseif ($statusCode === 401) {
                $this->line("   ❌ API Key invalide (Status: {$statusCode})");
                $this->line("   💡 Vérifiez que c'est une vraie Server Key");
            } elseif ($statusCode === 404) {
                $this->line("   ❌ API Key non autorisée pour FCM (Status: {$statusCode})");
                $this->line("   💡 L'API Key n'est pas une Server Key FCM");
            } else {
                $this->line("   ⚠️ Erreur inattendue (Status: {$statusCode})");
            }

            if (isset($result['error'])) {
                $this->line("   📝 Erreur: " . $result['error']);
            }

        } catch (\Exception $e) {
            $this->line("   ❌ Erreur de connexion: " . $e->getMessage());
        }
    }
}
