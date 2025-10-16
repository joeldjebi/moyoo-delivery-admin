<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseNotificationService
{
    private $serverKey;
    private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key');

        if (!$this->serverKey) {
            throw new Exception('Firebase Server Key non configuré. Vérifiez votre fichier .env');
        }
    }

    /**
     * Envoyer une notification à un token FCM spécifique
     */
    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        $payload = [
            'to' => $token,
            'notification' => $notification,
            'data' => $data,
            'priority' => 'high'
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envoyer une notification à plusieurs tokens FCM
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): array
    {
        $payload = [
            'registration_ids' => $tokens,
            'notification' => $notification,
            'data' => $data,
            'priority' => 'high'
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envoyer une notification à un topic
     */
    public function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => $notification,
            'data' => $data,
            'priority' => 'high'
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envoyer une notification de nouveau colis à un livreur
     */
    public function sendNewColisNotification($livreur, $colis): array
    {
        $notification = [
            'title' => 'Nouveau Colis Assigné',
            'body' => "Colis #{$colis->code} - {$colis->nom_client}",
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        $data = [
            'type' => 'new_colis',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'client_name' => $colis->nom_client,
            'client_address' => $colis->adresse_client,
            'amount' => (string) $colis->montant_a_encaisse,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de nouveau ramassage à un livreur
     */
    public function sendNewRamassageNotification($livreur, $ramassage): array
    {
        $notification = [
            'title' => 'Nouveau Ramassage Assigné',
            'body' => "Ramassage #{$ramassage->code_ramassage} - {$ramassage->marchand_name}",
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        $data = [
            'type' => 'new_ramassage',
            'ramassage_id' => (string) $ramassage->id,
            'ramassage_code' => $ramassage->code_ramassage,
            'marchand_name' => $ramassage->marchand_name,
            'marchand_address' => $ramassage->adresse_ramassage,
            'colis_count' => (string) count($ramassage->colis_data ?? []),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de colis livré à un marchand
     */
    public function sendColisDeliveredNotification($marchand, $colis): array
    {
        $notification = [
            'title' => 'Colis Livré',
            'body' => "Votre colis #{$colis->code} a été livré avec succès",
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        $data = [
            'type' => 'colis_delivered',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'client_name' => $colis->nom_client,
            'delivery_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        return $this->sendToToken($marchand->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de ramassage effectué à un marchand
     */
    public function sendRamassageCompletedNotification($marchand, $ramassage): array
    {
        $notification = [
            'title' => 'Ramassage Effectué',
            'body' => "Votre ramassage #{$ramassage->code_ramassage} a été effectué",
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        $data = [
            'type' => 'ramassage_completed',
            'ramassage_id' => (string) $ramassage->id,
            'ramassage_code' => $ramassage->code_ramassage,
            'colis_count' => (string) count($ramassage->colis_data ?? []),
            'completion_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        return $this->sendToToken($marchand->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de colis annulé
     */
    public function sendColisCancelledNotification($livreur, $colis, $reason = ''): array
    {
        $notification = [
            'title' => 'Colis Annulé',
            'body' => "Le colis #{$colis->code} a été annulé" . ($reason ? " - {$reason}" : ''),
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        $data = [
            'type' => 'colis_cancelled',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'reason' => $reason,
            'cancellation_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification personnalisée
     */
    public function sendCustomNotification($token, string $title, string $body, array $data = []): array
    {
        $notification = [
            'title' => $title,
            'body' => $body,
            'icon' => 'ic_notification',
            'sound' => 'default'
        ];

        return $this->sendToToken($token, $notification, $data);
    }

    /**
     * Exécuter la requête HTTP vers Firebase
     */
    private function sendRequest(array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            $result = $response->json();

            // Log de la réponse
            Log::info('Firebase Notification Sent', [
                'payload' => $payload,
                'response' => $result,
                'status_code' => $response->status()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Notification envoyée avec succès',
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification',
                    'error' => $result,
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('Firebase Notification Error', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valider un token FCM
     */
    public function validateToken(string $token): bool
    {
        // Test simple en envoyant une notification silencieuse
        $payload = [
            'to' => $token,
            'data' => ['test' => 'validation'],
            'priority' => 'high'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->fcmUrl, $payload);

        return $response->successful();
    }
}
