<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseNotificationServiceV2
{
    private $projectId;
    private $serviceAccountKey;
    private $accessToken;
    private $tokenExpiry;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->serviceAccountKey = config('services.firebase.service_account_key');

        if (!$this->projectId || !$this->serviceAccountKey) {
            throw new Exception('Configuration Firebase manquante. Vérifiez votre fichier .env');
        }
    }

    /**
     * Obtenir un token d'accès OAuth2
     */
    private function getAccessToken(): string
    {
        // Vérifier si le token est encore valide
        if ($this->accessToken && $this->tokenExpiry && now()->timestamp < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->createJWT()
        ]);

        if (!$response->successful()) {
            throw new Exception('Impossible d\'obtenir le token d\'accès Firebase: ' . $response->body());
        }

        $data = $response->json();
        $this->accessToken = $data['access_token'];
        $this->tokenExpiry = now()->timestamp + $data['expires_in'] - 60; // 1 minute de marge

        return $this->accessToken;
    }

    /**
     * Créer un JWT pour l'authentification
     */
    private function createJWT(): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = now()->timestamp;
        $payload = [
            'iss' => $this->serviceAccountKey['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        $signatureData = $headerEncoded . '.' . $payloadEncoded;

        if (!openssl_sign($signatureData, $signature, $this->serviceAccountKey['private_key'], 'SHA256')) {
            throw new Exception('Impossible de signer le JWT');
        }

        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Encodage Base64 URL-safe
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Envoyer une notification à un token FCM spécifique
     */
    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => $notification,
                'data' => $data,
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ]
                ]
            ]
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envoyer une notification à plusieurs tokens FCM
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): array
    {
        $payload = [
            'message' => [
                'tokens' => $tokens,
                'notification' => $notification,
                'data' => $data,
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ]
                ]
            ]
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Envoyer une notification à un topic
     */
    public function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => $notification,
                'data' => $data,
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ]
                ]
            ]
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
        ];

        return $this->sendToToken($token, $notification, $data);
    }

    /**
     * Exécuter la requête HTTP vers Firebase
     */
    private function sendRequest(array $payload): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            // Log de la réponse
            Log::info('Firebase Notification Sent (V2)', [
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
            Log::error('Firebase Notification Error (V2)', [
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
            'message' => [
                'token' => $token,
                'data' => ['test' => 'validation'],
                'android' => [
                    'priority' => 'high'
                ]
            ]
        ];

        try {
            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
