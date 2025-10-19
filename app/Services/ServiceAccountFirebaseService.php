<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ServiceAccountFirebaseService
{
    private $projectId = 'moyoo-fleet';
    private $accessToken;
    private $tokenExpiry;

    public function __construct()
    {
        // Vérifier la configuration
        if (!$this->projectId) {
            throw new Exception('FIREBASE_PROJECT_ID non configuré. Vérifiez votre fichier .env');
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
        $serviceAccountKey = config('services.firebase.service_account_key');

        if (!$serviceAccountKey || !isset($serviceAccountKey['private_key'])) {
            throw new Exception('Service Account Key non configuré correctement');
        }

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

        // Nettoyer la clé privée
        $privateKey = $serviceAccountKey['private_key'];
        if (strpos($privateKey, '\\n') !== false) {
            $privateKey = str_replace('\\n', "\n", $privateKey);
        }

        if (!openssl_sign($signatureData, $signature, $privateKey, 'SHA256')) {
            throw new Exception('Impossible de signer le JWT. Vérifiez la clé privée.');
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
     * Obtenir le nombre de colis depuis les données de ramassage
     */
    private function getColisCount($colisData): int
    {
        if (is_string($colisData)) {
            $decoded = json_decode($colisData, true);
            return is_array($decoded) ? count($decoded) : 0;
        }

        return is_array($colisData) ? count($colisData) : 0;
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
            'colis_count' => (string) $this->getColisCount($ramassage->colis_data ?? []),
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
            'colis_count' => (string) $this->getColisCount($ramassage->colis_data ?? []),
            'completion_date' => now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($marchand->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de nouveau colis créé
     */
    public function sendColisCreatedNotification($livreur, $colis): array
    {
        $notification = [
            'title' => '📦 Nouveau Colis Créé MOYOO',
            'body' => "Colis #{$colis->code} - {$colis->nom_client} - {$colis->adresse_client}",
        ];

        $data = [
            'type' => 'colis_created',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code ?? '',
            'client_name' => $colis->nom_client ?? '',
            'client_address' => $colis->adresse_client ?? '',
            'client_phone' => $colis->telephone_client ?? '',
            'amount' => (string) ($colis->montant_a_encaisse ?? '0'),
            'status' => (string) ($colis->statut ?? '0'),
            'created_at' => $colis->created_at ? $colis->created_at->toISOString() : now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
    }

    /**
     * Envoyer une notification de colis mis à jour
     */
    public function sendColisUpdatedNotification($livreur, $colis, $changes = []): array
    {
        $notification = [
            'title' => '📝 Colis Mis à Jour MOYOO',
            'body' => "Colis #{$colis->code} - {$colis->nom_client}",
        ];

        $data = [
            'type' => 'colis_updated',
            'colis_id' => (string) $colis->id,
            'colis_code' => $colis->code ?? '',
            'client_name' => $colis->nom_client ?? '',
            'client_address' => $colis->adresse_client ?? '',
            'client_phone' => $colis->telephone_client ?? '',
            'amount' => (string) ($colis->montant_a_encaisse ?? '0'),
            'status' => (string) ($colis->statut ?? '0'),
            'changes' => json_encode($changes),
            'updated_at' => $colis->updated_at ? $colis->updated_at->toISOString() : now()->toISOString(),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'app' => 'moyoo_fleet'
        ];

        return $this->sendToToken($livreur->fcm_token, $notification, $data);
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
            'message' => [
                'tokens' => $tokens,
                'notification' => $notification,
                'data' => array_merge($data, ['app' => 'moyoo_fleet']),
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
            ]
        ];

        return $this->sendRequest($payload);
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
            Log::info('MOYOO Firebase Notification Sent (Service Account)', [
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
            Log::error('MOYOO Firebase Notification Error (Service Account)', [
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
     * Envoyer une notification à l'admin lors de la fin d'une livraison
     */
    public function sendDeliveryCompletedNotificationToAdmin($colis, $livreur, $adminToken)
    {
        try {
            $payload = [
                'message' => [
                    'token' => $adminToken,
                    'notification' => [
                        'title' => 'Livraison Terminée',
                        'body' => "Le colis {$colis->code} a été livré par {$livreur->first_name} {$livreur->last_name}"
                    ],
                    'data' => [
                        'type' => 'delivery_completed',
                        'colis_id' => (string)$colis->id,
                        'colis_code' => $colis->code,
                        'livreur_id' => (string)$livreur->id,
                        'livreur_name' => "{$livreur->first_name} {$livreur->last_name}",
                        'client_name' => $colis->nom_client ?? 'N/A',
                        'timestamp' => now()->toISOString(),
                        'app' => 'moyoo_admin'
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'icon' => 'ic_notification',
                            'color' => '#4CAF50',
                            'sound' => 'default'
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ]
            ];

            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('Notification de livraison terminée envoyée à l\'admin', [
                    'colis_id' => $colis->id,
                    'colis_code' => $colis->code,
                    'livreur_id' => $livreur->id,
                    'admin_token' => substr($adminToken, 0, 20) . '...',
                    'response' => $result
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification de livraison terminée envoyée à l\'admin',
                    'response' => $result
                ];
            } else {
                Log::error('Erreur envoi notification livraison terminée à l\'admin', [
                    'colis_id' => $colis->id,
                    'error' => $result,
                    'status_code' => $response->status()
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification à l\'admin',
                    'error' => $result,
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('Erreur notification livraison terminée à l\'admin', [
                'colis_id' => $colis->id,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification à l\'admin: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à l'admin lors de la fin d'un ramassage
     */
    public function sendRamassageCompletedNotificationToAdmin($ramassage, $livreur, $adminToken)
    {
        try {
            $payload = [
                'message' => [
                    'token' => $adminToken,
                    'notification' => [
                        'title' => 'Ramassage Terminé',
                        'body' => "Le ramassage {$ramassage->code_ramassage} a été terminé par {$livreur->first_name} {$livreur->last_name}"
                    ],
                    'data' => [
                        'type' => 'ramassage_completed',
                        'ramassage_id' => (string)$ramassage->id,
                        'ramassage_code' => $ramassage->code_ramassage,
                        'livreur_id' => (string)$livreur->id,
                        'livreur_name' => "{$livreur->first_name} {$livreur->last_name}",
                        'marchand_name' => $ramassage->marchand->first_name ?? 'N/A',
                        'boutique_name' => $ramassage->boutique->libelle ?? 'N/A',
                        'nombre_colis' => (string)($ramassage->nombre_colis_reel ?? 0),
                        'timestamp' => now()->toISOString(),
                        'app' => 'moyoo_admin'
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'icon' => 'ic_notification',
                            'color' => '#2196F3',
                            'sound' => 'default'
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ]
            ];

            $accessToken = $this->getAccessToken();
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('Notification de ramassage terminé envoyée à l\'admin', [
                    'ramassage_id' => $ramassage->id,
                    'ramassage_code' => $ramassage->code_ramassage,
                    'livreur_id' => $livreur->id,
                    'admin_token' => substr($adminToken, 0, 20) . '...',
                    'response' => $result
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification de ramassage terminé envoyée à l\'admin',
                    'response' => $result
                ];
            } else {
                Log::error('Erreur envoi notification ramassage terminé à l\'admin', [
                    'ramassage_id' => $ramassage->id,
                    'error' => $result,
                    'status_code' => $response->status()
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification à l\'admin',
                    'error' => $result,
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('Erreur notification ramassage terminé à l\'admin', [
                'ramassage_id' => $ramassage->id,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification à l\'admin: ' . $e->getMessage(),
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
                'message' => [
                    'token' => $token,
                    'data' => ['test' => 'validation', 'app' => 'moyoo_fleet'],
                    'android' => [
                        'priority' => 'high'
                    ]
                ]
            ];

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
