<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PickupCompletedNotification extends Notification
{
    use Queueable;

    protected $ramassage;
    protected $livreur;

    /**
     * Create a new notification instance.
     */
    public function __construct($ramassage, $livreur)
    {
        $this->ramassage = $ramassage;
        $this->livreur = $livreur;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pickup_completed',
            'title' => 'Ramassage Terminé 📦',
            'message' => "Le livreur {$this->livreur->nom} a terminé le ramassage #{$this->ramassage->id}",
            'ramassage_id' => $this->ramassage->id,
            'livreur_id' => $this->livreur->id,
            'livreur_nom' => $this->livreur->nom,
            'expediteur' => $this->ramassage->expediteur,
            'adresse' => $this->ramassage->adresse_ramassage,
            'entreprise_id' => $this->ramassage->entreprise_id,
            'icon' => 'ti-package',
            'color' => 'info',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'pickup_completed',
            'data' => $this->toArray($notifiable),
        ];
    }
}
