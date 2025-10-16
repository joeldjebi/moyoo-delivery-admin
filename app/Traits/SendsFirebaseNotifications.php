<?php

namespace App\Traits;

use App\Services\ServiceAccountFirebaseService;
use Illuminate\Support\Facades\Log;

trait SendsFirebaseNotifications
{
    /**
     * Instance du service Firebase
     */
    protected $firebaseService;

    /**
     * Obtenir l'instance du service Firebase
     */
    protected function getFirebaseService(): ServiceAccountFirebaseService
    {
        if (!$this->firebaseService) {
            $this->firebaseService = app(ServiceAccountFirebaseService::class);
        }
        return $this->firebaseService;
    }

    /**
     * Envoyer une notification de nouveau colis
     */
    protected function sendNewColisNotification($livreur, $colis): array
    {
        try {
            if (!$livreur->fcm_token) {
                Log::warning('FCM token manquant pour le livreur', ['livreur_id' => $livreur->id]);
                return ['success' => false, 'message' => 'FCM token manquant'];
            }

            return $this->getFirebaseService()->sendNewColisNotification($livreur, $colis);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification nouveau colis', [
                'livreur_id' => $livreur->id,
                'colis_id' => $colis->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de nouveau ramassage
     */
    protected function sendNewRamassageNotification($livreur, $ramassage): array
    {
        try {
            if (!$livreur->fcm_token) {
                Log::warning('FCM token manquant pour le livreur', ['livreur_id' => $livreur->id]);
                return ['success' => false, 'message' => 'FCM token manquant'];
            }

            return $this->getFirebaseService()->sendNewRamassageNotification($livreur, $ramassage);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification nouveau ramassage', [
                'livreur_id' => $livreur->id,
                'ramassage_id' => $ramassage->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de colis livré
     */
    protected function sendColisDeliveredNotification($marchand, $colis): array
    {
        try {
            if (!$marchand->fcm_token) {
                Log::warning('FCM token manquant pour le marchand', ['marchand_id' => $marchand->id]);
                return ['success' => false, 'message' => 'FCM token manquant'];
            }

            return $this->getFirebaseService()->sendColisDeliveredNotification($marchand, $colis);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification colis livré', [
                'marchand_id' => $marchand->id,
                'colis_id' => $colis->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de ramassage effectué
     */
    protected function sendRamassageCompletedNotification($marchand, $ramassage): array
    {
        try {
            if (!$marchand->fcm_token) {
                Log::warning('FCM token manquant pour le marchand', ['marchand_id' => $marchand->id]);
                return ['success' => false, 'message' => 'FCM token manquant'];
            }

            return $this->getFirebaseService()->sendRamassageCompletedNotification($marchand, $ramassage);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification ramassage effectué', [
                'marchand_id' => $marchand->id,
                'ramassage_id' => $ramassage->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de colis annulé
     */
    protected function sendColisCancelledNotification($livreur, $colis, $reason = ''): array
    {
        try {
            if (!$livreur->fcm_token) {
                Log::warning('FCM token manquant pour le livreur', ['livreur_id' => $livreur->id]);
                return ['success' => false, 'message' => 'FCM token manquant'];
            }

            return $this->getFirebaseService()->sendColisCancelledNotification($livreur, $colis, $reason);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification colis annulé', [
                'livreur_id' => $livreur->id,
                'colis_id' => $colis->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification personnalisée
     */
    protected function sendCustomNotification($token, string $title, string $body, array $data = []): array
    {
        try {
            return $this->getFirebaseService()->sendCustomNotification($token, $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification personnalisée', [
                'token' => $token,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification à plusieurs tokens
     */
    protected function sendToMultipleTokens(array $tokens, array $notification, array $data = []): array
    {
        try {
            return $this->getFirebaseService()->sendToTokens($tokens, $notification, $data);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification multiple', [
                'tokens_count' => count($tokens),
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification à un topic
     */
    protected function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        try {
            return $this->getFirebaseService()->sendToTopic($topic, $notification, $data);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification topic', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de nouveau colis créé
     */
    protected function sendColisCreatedNotification($livreur, $colis): array
    {
        try {
            return $this->getFirebaseService()->sendColisCreatedNotification($livreur, $colis);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification colis créé', [
                'livreur_id' => $livreur->id ?? 'unknown',
                'colis_id' => $colis->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de colis mis à jour
     */
    protected function sendColisUpdatedNotification($livreur, $colis, $changes = []): array
    {
        try {
            return $this->getFirebaseService()->sendColisUpdatedNotification($livreur, $colis, $changes);
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification colis mis à jour', [
                'livreur_id' => $livreur->id ?? 'unknown',
                'colis_id' => $colis->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}
