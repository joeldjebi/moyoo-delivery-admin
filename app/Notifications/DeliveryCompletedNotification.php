<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryCompletedNotification extends Notification
{
    use Queueable;

    protected $colis;
    protected $livreur;

    /**
     * Create a new notification instance.
     */
    public function __construct($colis, $livreur)
    {
        $this->colis = $colis;
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
            'type' => 'delivery_completed',
            'title' => 'Livraison Terminée 🚚',
            'message' => "Le livreur {$this->livreur->nom} a terminé la livraison du colis #{$this->colis->id}",
            'colis_id' => $this->colis->id,
            'livreur_id' => $this->livreur->id,
            'livreur_nom' => $this->livreur->nom,
            'destinataire' => $this->colis->destinataire,
            'adresse' => $this->colis->adresse_livraison,
            'entreprise_id' => $this->colis->packageColis?->communeZone?->entreprise_id,
            'icon' => 'ti-truck',
            'color' => 'success',
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
            'type' => 'delivery_completed',
            'data' => $this->toArray($notifiable),
        ];
    }
}
