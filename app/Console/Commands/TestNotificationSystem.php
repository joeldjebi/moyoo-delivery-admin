<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\DeliveryCompletedNotification;
use App\Notifications\PickupCompletedNotification;
use App\Notifications\NewColisNotification;
use App\Models\Colis;
use App\Models\Ramassage;
use App\Models\Livreur;

class TestNotificationSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test du système de notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test du système de notifications...');

        // Trouver un admin
        $admin = User::whereIn('user_type', ['admin', 'entreprise_user'])->first();

        if (!$admin) {
            $this->error('❌ Aucun admin trouvé');
            return;
        }

        $this->info("✅ Admin trouvé: {$admin->first_name} {$admin->last_name}");

        // Test 1: Notification de livraison terminée
        $this->info('📦 Test 1: Notification de livraison terminée...');

        $colis = Colis::first();
        $livreur = Livreur::first();

        if ($colis && $livreur) {
            $admin->notify(new DeliveryCompletedNotification($colis, $livreur));
            $this->info('✅ Notification de livraison envoyée');
        } else {
            $this->warn('⚠️ Pas de colis ou livreur trouvé pour le test');
        }

        // Test 2: Notification de ramassage terminé
        $this->info('📦 Test 2: Notification de ramassage terminé...');

        $ramassage = Ramassage::first();

        if ($ramassage && $livreur) {
            $admin->notify(new PickupCompletedNotification($ramassage, $livreur));
            $this->info('✅ Notification de ramassage envoyée');
        } else {
            $this->warn('⚠️ Pas de ramassage trouvé pour le test');
        }

        // Test 3: Notification de nouveau colis
        $this->info('📦 Test 3: Notification de nouveau colis...');

        if ($colis) {
            $admin->notify(new NewColisNotification($colis));
            $this->info('✅ Notification de nouveau colis envoyée');
        }

        // Vérifier les notifications
        $unreadCount = $admin->unreadNotificationsCount();
        $this->info("📊 Notifications non lues: {$unreadCount}");

        $this->info('🎉 Tests terminés ! Vérifiez l\'interface web pour voir les notifications.');
    }
}
