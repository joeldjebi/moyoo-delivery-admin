<?php

/**
 * Script pour activer le module Stock pour un plan tarifaire
 *
 * Usage: php artisan tinker < scripts/activate-stock-module-for-plan.php
 * Ou copier-coller le contenu dans tinker
 */

use App\Models\Module;
use App\Models\PricingPlan;

// 1. Récupérer le module Stock
$module = Module::where('slug', 'stock_management')->first();

if (!$module) {
    echo "❌ Module Stock non trouvé. Exécutez d'abord: php artisan db:seed --class=StockModuleSeeder\n";
    exit(1);
}

echo "✅ Module trouvé: {$module->name}\n";
echo "   Prix: " . number_format($module->price, 0, ',', ' ') . " {$module->currency}\n";
echo "   Optionnel: " . ($module->is_optional ? 'Oui' : 'Non') . "\n\n";

// 2. Lister tous les plans tarifaires
echo "📋 Plans tarifaires disponibles:\n";
$plans = PricingPlan::active()->get();
foreach ($plans as $plan) {
    echo "   - ID {$plan->id}: {$plan->name} ({$plan->price} {$plan->currency})\n";
}

echo "\n";

// 3. Activer le module pour tous les plans Premium (exemple)
// Vous pouvez modifier cette partie pour activer pour un plan spécifique

$premiumPlans = PricingPlan::whereIn('name', ['Premium', 'Premium Annuel'])->get();

if ($premiumPlans->isEmpty()) {
    echo "⚠️  Aucun plan Premium trouvé\n";
    echo "Pour activer manuellement pour un plan spécifique:\n";
    echo "   \$plan = PricingPlan::find(PLAN_ID);\n";
    echo "   \$plan->attachModule({$module->id}, true);\n";
    exit(0);
}

foreach ($premiumPlans as $plan) {
    // Vérifier si le module est déjà attaché
    $isAttached = $plan->modules()->where('module_id', $module->id)->exists();

    if ($isAttached) {
        // Mettre à jour pour activer
        $plan->toggleModule($module->id, true);
        echo "✅ Module activé pour le plan: {$plan->name}\n";
    } else {
        // Attacher et activer
        $plan->attachModule($module->id, true);
        echo "✅ Module attaché et activé pour le plan: {$plan->name}\n";
    }
}

echo "\n✅ Terminé! Le module Stock est maintenant disponible comme option lors du paiement des plans Premium.\n";
