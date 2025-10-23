<?php

namespace App\Traits;

trait NotifiesWithEntreprise
{
    /**
     * Send a notification with entreprise_id automatically set
     */
    protected function notifyWithEntreprise($notifiable, $notification, $entrepriseId = null)
    {
        // Si l'entreprise_id n'est pas fourni, essayer de l'obtenir du notifiable
        if (!$entrepriseId && method_exists($notifiable, 'entreprise_id')) {
            $entrepriseId = $notifiable->entreprise_id;
        }

        // Si l'entreprise_id n'est toujours pas disponible, essayer de l'obtenir via une relation
        if (!$entrepriseId && method_exists($notifiable, 'entreprise')) {
            $entrepriseId = $notifiable->entreprise?->id;
        }

        // Envoyer la notification avec l'entreprise_id
        $notifiable->notify($notification);

        // Mettre à jour l'entreprise_id dans la notification créée
        if ($entrepriseId) {
            $latestNotification = $notifiable->notifications()->latest()->first();
            if ($latestNotification) {
                $latestNotification->update(['entreprise_id' => $entrepriseId]);
            }
        }
    }

    /**
     * Send multiple notifications with entreprise_id automatically set
     */
    protected function notifyMultipleWithEntreprise($notifiables, $notification, $entrepriseId = null)
    {
        foreach ($notifiables as $notifiable) {
            $this->notifyWithEntreprise($notifiable, $notification, $entrepriseId);
        }
    }
}
