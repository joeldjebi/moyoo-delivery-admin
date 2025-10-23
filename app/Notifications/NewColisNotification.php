<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewColisNotification extends Notification
{
    use Queueable;

    protected $colis;

    /**
     * Create a new notification instance.
     */
    public function __construct($colis)
    {
        $this->colis = $colis;
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
            'type' => 'new_colis',
            'title' => 'Nouveau Colis 📦',
            'message' => "Un nouveau colis a été créé pour {$this->colis->destinataire}",
            'colis_id' => $this->colis->id,
            'destinataire' => $this->colis->destinataire,
            'adresse' => $this->colis->adresse_livraison,
            'montant' => $this->colis->montant,
            'entreprise_id' => $this->colis->packageColis?->communeZone?->entreprise_id,
            'icon' => 'ti-package',
            'color' => 'primary',
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
            'type' => 'new_colis',
            'data' => $this->toArray($notifiable),
        ];
    }
}
