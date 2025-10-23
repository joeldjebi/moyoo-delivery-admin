<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean {--all : Supprimer toutes les notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les notifications de test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Nettoyage des notifications...');

        if ($this->option('all')) {
            // Supprimer toutes les notifications
            $count = DB::table('notifications')->count();
            DB::table('notifications')->delete();
            $this->info("✅ {$count} notifications supprimées");
        } else {
            // Supprimer seulement les notifications de test (plus anciennes que 1 heure)
            $count = DB::table('notifications')
                ->where('created_at', '<', now()->subHour())
                ->count();

            DB::table('notifications')
                ->where('created_at', '<', now()->subHour())
                ->delete();

            $this->info("✅ {$count} notifications de test supprimées");
        }

        // Afficher le nombre de notifications restantes
        $remaining = DB::table('notifications')->count();
        $this->info("📊 Notifications restantes: {$remaining}");

        $this->info('🎉 Nettoyage terminé !');
    }
}
