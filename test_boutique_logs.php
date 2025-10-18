<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

echo "🧪 Test de création de boutique avec logs...\n";

try {
    // Vérifier que le marchand ID 3 existe
    $marchand = \App\Models\Marchand::where('id', 3)->first();

    if (!$marchand) {
        echo "❌ Le marchand avec l'ID 3 n'existe pas\n";
        exit(1);
    }

    echo "✅ Marchand trouvé: {$marchand->first_name} {$marchand->last_name} (ID: {$marchand->id})\n";
    echo "   Entreprise ID: {$marchand->entreprise_id}\n";

    // Vérifier s'il existe déjà une boutique avec ce nom pour ce marchand
    $existingBoutique = \App\Models\Boutique::where('marchand_id', 3)
        ->where('libelle', 'Moyoo Boutique Fleur')
        ->first();

    if ($existingBoutique) {
        echo "⚠️  Une boutique avec le nom 'Moyoo Boutique Fleur' existe déjà:\n";
        echo "   Boutique ID: {$existingBoutique->id}\n";
        echo "   Créée le: {$existingBoutique->created_at}\n";
        echo "   Statut: {$existingBoutique->status}\n";
        echo "   Téléphone: {$existingBoutique->mobile}\n";
        echo "   Créée par: {$existingBoutique->created_by}\n";
        exit(0);
    }

    // Créer la boutique
    echo "🔄 Création de la boutique...\n";

    $boutique = \App\Models\Boutique::create([
        'libelle' => 'Moyoo Boutique Fleur',
        'mobile' => '+225 07 12 34 56 78',
        'adresse' => 'Adresse test pour la boutique',
        'adresse_gps' => 'https://maps.google.com/test',
        'cover_image' => null,
        'marchand_id' => 3,
        'status' => 'active',
        'entreprise_id' => $marchand->entreprise_id,
        'created_by' => 1
    ]);

    echo "✅ Boutique créée avec succès!\n";
    echo "   ID: {$boutique->id}\n";
    echo "   Nom: {$boutique->libelle}\n";
    echo "   Marchand ID: {$boutique->marchand_id}\n";
    echo "   Statut: {$boutique->status}\n";
    echo "   Téléphone: {$boutique->mobile}\n";
    echo "   Adresse: {$boutique->adresse}\n";
    echo "   Entreprise ID: {$boutique->entreprise_id}\n";
    echo "   Créée par: {$boutique->created_by}\n";
    echo "   Créée le: {$boutique->created_at}\n";

    // Vérifier la relation avec le marchand
    $boutiqueWithMarchand = \App\Models\Boutique::with('marchand')->find($boutique->id);
    echo "   Marchand associé: {$boutiqueWithMarchand->marchand->first_name} {$boutiqueWithMarchand->marchand->last_name}\n";

    echo "\n🎉 Test réussi! La boutique a été créée correctement.\n";
    echo "📋 Vérifiez les logs Laravel pour voir les détails du processus.\n";

} catch (\Exception $e) {
    echo "❌ Erreur lors de la création: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
