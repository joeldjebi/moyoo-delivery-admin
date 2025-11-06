<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use App\Services\TenantBootstrapService;
use Illuminate\Support\Facades\Log;

class RepairBootstrapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bootstrap:repair {--entreprise_id= : ID de l\'entreprise spécifique} {--force : Réparer même si les données semblent complètes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réparer les données manquantes du bootstrap pour les entreprises';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Vérification et réparation des données bootstrap...');
        $this->newLine();

        $bootstrapService = app(TenantBootstrapService::class);
        $force = $this->option('force');

        // Récupérer les entreprises
        $entrepriseId = $this->option('entreprise_id');

        if ($entrepriseId) {
            $entreprises = Entreprise::where('id', $entrepriseId)->get();
        } else {
            $entreprises = Entreprise::all();
        }

        $repaired = 0;
        $alreadyComplete = 0;
        $errors = 0;

        foreach ($entreprises as $entreprise) {
            try {
                // Récupérer le créateur de l'entreprise
                $userId = $entreprise->created_by ?? 1;

                $this->info("Vérification de l'entreprise #{$entreprise->id}: {$entreprise->name}");

                // Vérifier les données
                $verification = $bootstrapService->verifyBootstrap($entreprise->id, $userId);

                if ($verification['success'] && !$force) {
                    $alreadyComplete++;
                    $this->info("   ✅ Toutes les données sont présentes ({$verification['passed_checks']}/{$verification['total_checks']})");
                    continue;
                }

                if (!$verification['success']) {
                    $this->warn("   ⚠️  Données manquantes: " . implode(', ', $verification['missing']));
                    $this->info("   🔧 Réparation en cours...");

                    $bootstrapService->repairMissingData($entreprise->id, $userId, $verification['missing']);

                    // Vérifier à nouveau
                    $verificationAfter = $bootstrapService->verifyBootstrap($entreprise->id, $userId);

                    if ($verificationAfter['success']) {
                        $repaired++;
                        $this->info("   ✅ Réparation réussie ({$verificationAfter['passed_checks']}/{$verificationAfter['total_checks']})");
                    } else {
                        $errors++;
                        $this->error("   ❌ Réparation incomplète. Données toujours manquantes: " . implode(', ', $verificationAfter['missing']));
                    }
                } else {
                    $alreadyComplete++;
                    $this->info("   ✅ Toutes les données sont présentes");
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("   ❌ Erreur pour l'entreprise #{$entreprise->id}: {$e->getMessage()}");
                Log::error('Erreur lors de la réparation bootstrap', [
                    'entreprise_id' => $entreprise->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Résumé:");
        $this->info("   - Réparées: {$repaired}");
        $this->info("   - Déjà complètes: {$alreadyComplete}");
        $this->info("   - Erreurs: {$errors}");

        return 0;
    }
}

