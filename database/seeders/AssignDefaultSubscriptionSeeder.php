<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubscriptionPlan;

class AssignDefaultSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le plan Free
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        
        if (!$freePlan) {
            $this->command->error('Plan Free non trouvé. Exécutez d\'abord SubscriptionPlanSeeder.');
            return;
        }

        // Assigner le plan Free à tous les utilisateurs qui n'ont pas d'abonnement
        $usersWithoutSubscription = User::whereNull('subscription_plan_id')->get();
        
        $this->command->info("Assignation du plan Free à {$usersWithoutSubscription->count()} utilisateurs...");

        foreach ($usersWithoutSubscription as $user) {
            $user->assignSubscriptionPlan($freePlan->id, true); // true = période d'essai
            $this->command->info("Plan Free assigné à {$user->full_name} (ID: {$user->id})");
        }

        $this->command->info("✅ Assignation terminée !");
    }
}
