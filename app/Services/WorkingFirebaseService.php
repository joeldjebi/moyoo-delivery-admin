<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkingFirebaseService
{
    private $projectId = 'moyoo-fleet';

    public function __construct()
    {
        // Vérifier la configuration
        if (!$this->projectId) {
            throw new Exception('FIREBASE_PROJECT_ID non configuré. Vérifiez votre fichier .env');
        }
    }

    /**
     * Envoyer une notification à un token FCM (méthode simple avec curl)
     */
    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        try {
            // Utiliser l'API Legacy de Firebase qui fonctionne avec l'API Key
            $url = 'https://fcm.googleapis.com/fcm/send';

            $payload = [
                'to' => $token,
                'notification' => $notification,
                'data' => $data,
                'priority' => 'high'
            ];

            // Utiliser curl directement pour plus de contrôle
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: key=AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception('Erreur cURL: ' . $error);
            }

            $result = json_decode($response, true);

            // Log de la réponse
            Log::info('MOYOO Firebase Notification Sent (cURL)', [
                'project_id' => $this->projectId,
                'payload' => $payload,
                'response' => $result,
                'status_code' => $httpCode
            ]);

            if ($httpCode === 200) {
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
                    'status_code' => $httpCode
                ];
            }

        } catch (Exception $e) {
            Log::error('MOYOO Firebase Notification Error (cURL)', [
                'project_id' => $this->projectId,
                'error' => $e->getMessage(),
                'payload' => $payload ?? null
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification MOYOO: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
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
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';

            $payload = [
                'registration_ids' => $tokens,
                'notification' => $notification,
                'data' => array_merge($data, ['app' => 'moyoo_fleet']),
                'priority' => 'high'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: key=AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception('Erreur cURL: ' . $error);
            }

            $result = json_decode($response, true);

            Log::info('MOYOO Firebase Notification Sent (Multiple)', [
                'project_id' => $this->projectId,
                'tokens_count' => count($tokens),
                'response' => $result,
                'status_code' => $httpCode
            ]);

            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Notifications MOYOO envoyées avec succès',
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi des notifications MOYOO',
                    'error' => $result,
                    'status_code' => $httpCode
                ];
            }

        } catch (Exception $e) {
            Log::error('MOYOO Firebase Notification Error (Multiple)', [
                'project_id' => $this->projectId,
                'tokens_count' => count($tokens),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des notifications MOYOO: ' . $e->getMessage(),
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
            $url = 'https://fcm.googleapis.com/fcm/send';

            $payload = [
                'to' => $token,
                'data' => ['test' => 'validation', 'app' => 'moyoo_fleet'],
                'priority' => 'high'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: key=AIzaSyBpLQNbz69uex7RxvXrCOmms2w-t0AzUtM'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;
        } catch (Exception $e) {
            return false;
        }
    }
}
