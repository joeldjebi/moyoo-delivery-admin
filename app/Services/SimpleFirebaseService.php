<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SimpleFirebaseService
{
    private $projectId;
    private $accessToken;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');

        if (!$this->projectId) {
            throw new Exception('FIREBASE_PROJECT_ID non configuré. Vérifiez votre fichier .env');
        }
    }

    /**
     * Obtenir un token d'accès via gcloud (méthode simple)
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        // Méthode 1: Via gcloud CLI (si installé)
        $gcloudToken = $this->getGcloudToken();
        if ($gcloudToken) {
            $this->accessToken = $gcloudToken;
            return $this->accessToken;
        }

        // Méthode 2: Via service account key
        $serviceAccountToken = $this->getServiceAccountToken();
        if ($serviceAccountToken) {
            $this->accessToken = $serviceAccountToken;
            return $this->accessToken;
        }

        throw new Exception('Impossible d\'obtenir un token d\'accès Firebase');
    }

    /**
     * Obtenir un token via gcloud CLI
     */
    private function getGcloudToken(): ?string
    {
        try {
            $output = shell_exec('gcloud auth print-access-token 2>/dev/null');
            return trim($output) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtenir un token via service account
     */
    private function getServiceAccountToken(): ?string
    {
        $serviceAccountKey = config('services.firebase.service_account_key');

        if (!$serviceAccountKey) {
            return null;
        }

        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->createJWT($serviceAccountKey)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'];
            }
        } catch (Exception $e) {
            Log::error('Erreur obtention token service account', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Créer un JWT pour l'authentification
     */
    private function createJWT(array $serviceAccountKey): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = now()->timestamp;
        $payload = [
            'iss' => $serviceAccountKey['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        $signatureData = $headerEncoded . '.' . $payloadEncoded;

        if (!openssl_sign($signatureData, $signature, $serviceAccountKey['private_key'], 'SHA256')) {
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
     * Envoyer une notification à un token FCM
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
     * Envoyer une notification à plusieurs tokens
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
     * Envoyer une notification de nouveau colis
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
     * Envoyer une notification de nouveau ramassage
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
     * Envoyer une notification de colis livré
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
            Log::info('Firebase Notification Sent (Simple)', [
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
            Log::error('Firebase Notification Error (Simple)', [
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
}
