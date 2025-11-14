<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Module;
use App\Models\PricingPlan;

class AttachReversementModuleToPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:attach-reversement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attacher le module reversement_management à tous les pricing plans';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = Module::where('slug', 'reversement_management')->first();

        if (!$module) {
            $this->error('Le module reversement_management n\'existe pas. Exécutez d\'abord: php artisan db:seed --class=ReversementModuleSeeder');
            return self::FAILURE;
        }

        $plans = PricingPlan::all();

        if ($plans->isEmpty()) {
            $this->warn('Aucun pricing plan trouvé.');
            return self::SUCCESS;
        }

        $attachedCount = 0;
        foreach ($plans as $plan) {
            // Vérifier si le module est déjà attaché
            $isAttached = $plan->modules()->where('module_id', $module->id)->exists();

            if (!$isAttached) {
                $plan->attachModule($module->id, true);
                $this->info("Module attaché au plan: {$plan->name}");
                $attachedCount++;
            } else {
                // S'assurer que le module est activé
                $plan->toggleModule($module->id, true);
                $this->line("Module déjà attaché au plan: {$plan->name} (activé)");
            }
        }

        $this->info("✅ {$attachedCount} plan(s) mis à jour avec le module reversement_management");

        return self::SUCCESS;
    }
}
