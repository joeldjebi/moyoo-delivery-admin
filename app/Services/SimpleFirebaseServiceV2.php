<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SimpleFirebaseServiceV2
{
    private $projectId = 'moyoo-fleet';
    private $apiKey = 'AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM';

    public function __construct()
    {
        // Vérifier la configuration
        if (!$this->projectId || !$this->apiKey) {
            throw new Exception('Configuration Firebase manquante. Vérifiez votre fichier .env');
        }
    }

    /**
     * Envoyer une notification à un token FCM
     */
    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        $payload = [
            'to' => $token,
            'notification' => $notification,
            'data' => $data,
            'priority' => 'high',
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'icon' => 'ic_notification',
                    'color' => '#FF6B35', // Couleur MOYOO
                    'sound' => 'default'
                ]
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10'
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
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
            'title' => '📦 Nouveau Colis MOYOO',
            'body' => "Colis #{$colis->code} - {$colis->nom_client}",
        ];

        $data = [
            'type' => 'new_colis',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'client_name' => $colis->nom_client,
            'client_address' => $colis->adresse_client,
            'amount' => (string) $colis->montant_a_encaisse,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de nouveau ramassage à un livreur
     */
    public function sendNewRamassageNotification($livreur, $ramassage): array
    {
        $notification = [
            'title' => '🚚 Nouveau Ramassage MOYOO',
            'body' => "Ramassage #{$ramassage->code_ramassage} - {$ramassage->marchand_name}",
        ];

        $data = [
            'type' => 'new_ramassage',
            'ramassage_id' => (string) $ramassage->id,
            'ramassage_code' => $ramassage->code_ramassage,
            'marchand_name' => $ramassage->marchand_name,
            'marchand_address' => $ramassage->adresse_ramassage,
            'colis_count' => (string) count($ramassage->colis_data ?? []),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de colis livré à un marchand
     */
    public function sendColisDeliveredNotification($marchand, $colis): array
    {
        $notification = [
            'title' => '✅ Colis Livré MOYOO',
            'body' => "Votre colis #{$colis->code} a été livré avec succès",
        ];

        $data = [
            'type' => 'colis_delivered',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'client_name' => $colis->nom_client,
            'delivery_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($marchand->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de ramassage effectué à un marchand
     */
    public function sendRamassageCompletedNotification($marchand, $ramassage): array
    {
        $notification = [
            'title' => '✅ Ramassage Effectué MOYOO',
            'body' => "Votre ramassage #{$ramassage->code_ramassage} a été effectué",
        ];

        $data = [
            'type' => 'ramassage_completed',
            'ramassage_id' => (string) $ramassage->id,
            'ramassage_code' => $ramassage->code_ramassage,
            'colis_count' => (string) count($ramassage->colis_data ?? []),
            'completion_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($marchand->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de colis annulé
     */
    public function sendColisCancelledNotification($livreur, $colis, $reason = ''): array
    {
        $notification = [
            'title' => '❌ Colis Annulé MOYOO',
            'body' => "Le colis #{$colis->code} a été annulé" . ($reason ? " - {$reason}" : ''),
        ];

        $data = [
            'type' => 'colis_cancelled',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code,
            'reason' => $reason,
            'cancellation_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
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

        $data['app'] = 'moyoo_fleet';
        $data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        return $this->sendToToken($token, $notification, $data);
    }

    /**
     * Envoyer une notification à plusieurs tokens
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): array
    {
        $payload = [
            'registration_ids' => $tokens,
            'notification' => $notification,
            'data' => array_merge($data, ['app' => 'moyoo_fleet']),
            'priority' => 'high',
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'icon' => 'ic_notification',
                    'color' => '#FF6B35',
                    'sound' => 'default'
                ]
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10'
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
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
            'to' => '/topics/' . $topic,
            'notification' => $notification,
            'data' => array_merge($data, ['app' => 'moyoo_fleet']),
            'priority' => 'high',
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'icon' => 'ic_notification',
                    'color' => '#FF6B35',
                    'sound' => 'default'
                ]
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10'
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
                    ]
                ]
            ]
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Exécuter la requête HTTP vers Firebase (Legacy API)
     */
    private function sendRequest(array $payload): array
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            // Log de la réponse
            Log::info('MOYOO Firebase Notification Sent (Legacy API)', [
                'project_id' => $this->projectId,
                'payload' => $payload,
                'response' => $result,
                'status_code' => $response->status()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Notification MOYOO envoyée avec succès',
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification MOYOO',
                    'error' => $result,
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('MOYOO Firebase Notification Error (Legacy API)', [
                'project_id' => $this->projectId,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification MOYOO: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valider un token FCM
     */
    public function validateToken(string $token): bool
    {
        try {
            $payload = [
                'to' => $token,
                'data' => ['test' => 'validation', 'app' => 'moyoo_fleet'],
                'priority' => 'high'
            ];

            $url = 'https://fcm.googleapis.com/fcm/send';

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
