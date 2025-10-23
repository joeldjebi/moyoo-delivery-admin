<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Historique_livraison;
use App\Models\Colis;
use App\Models\PackageColis;
use Illuminate\Support\Facades\DB;

class CleanTodayTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:clean-today-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les données de test créées aujourd\'hui';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Nettoyage des données de test d\'aujourd\'hui...');

        DB::beginTransaction();

        try {
            // Supprimer les historiques de livraison créés aujourd'hui
            $todayHistories = Historique_livraison::whereDate('created_at', today())
                                                 ->where('created_by', '1')
                                                 ->get();

            $deletedHistories = $todayHistories->count();
            $todayHistories->each->delete();

            // Supprimer les colis créés aujourd'hui
            $todayColis = Colis::whereDate('created_at', today())
                              ->where('created_by', 1)
                              ->get();

            $deletedColis = $todayColis->count();
            $todayColis->each->delete();

            // Supprimer les packages créés aujourd'hui
            $todayPackages = PackageColis::whereDate('created_at', today())
                                        ->where('created_by', 1)
                                        ->get();

            $deletedPackages = $todayPackages->count();
            $todayPackages->each->delete();

            DB::commit();

            $this->info('✅ Nettoyage terminé !');
            $this->info("🗑️ {$deletedHistories} historiques de livraison supprimés");
            $this->info("🗑️ {$deletedColis} colis supprimés");
            $this->info("🗑️ {$deletedPackages} packages supprimés");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erreur lors du nettoyage : ' . $e->getMessage());
        }
    }
}
