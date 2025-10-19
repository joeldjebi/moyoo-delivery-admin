<?php

require_once 'vendor/autoload.php';

use App\Models\User;

echo "=== Configuration du token FCM pour l'admin ===\n\n";

// Trouver le premier admin
$admin = User::where('user_type', 'admin')->first();

if (!$admin) {
    echo "❌ Aucun admin trouvé dans la base de données\n";
    exit;
}

echo "✅ Admin trouvé: {$admin->first_name} {$admin->last_name} (ID: {$admin->id})\n";

// Simuler un token FCM (en production, ce serait un vrai token)
$fakeToken = 'fake_fcm_token_' . time() . '_' . rand(1000, 9999);

// Mettre à jour le token FCM
$admin->update(['fcm_token' => $fakeToken]);

echo "✅ Token FCM simulé enregistré: " . substr($fakeToken, 0, 20) . "...\n";
echo "   Token complet: {$fakeToken}\n\n";

echo "=== Instructions pour l'utilisation ===\n";
echo "1. L'admin peut maintenant recevoir des notifications push\n";
echo "2. Quand un livreur termine une livraison, l'admin recevra une notification\n";
echo "3. Quand un livreur termine un ramassage, l'admin recevra une notification\n";
echo "4. En production, utilisez l'API pour enregistrer le vrai token FCM:\n";
echo "   POST /api/fcm-token\n";
echo "   Headers: Authorization: Bearer {token_admin}\n";
echo "   Body: {\"fcm_token\": \"votre_vrai_token_fcm\"}\n\n";

echo "Configuration terminée !\n";
