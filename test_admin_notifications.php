<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Colis;
use App\Models\Ramassage;
use App\Models\Livreur;
use App\Services\ServiceAccountFirebaseService;

echo "=== Test des notifications admin ===\n\n";

// 1. Vérifier qu'il y a un admin avec un token FCM
$admin = User::where('user_type', 'admin')
    ->whereNotNull('fcm_token')
    ->first();

if (!$admin) {
    echo "❌ Aucun admin trouvé avec un token FCM\n";
    echo "Pour tester, vous devez d'abord enregistrer un token FCM pour l'admin via l'API:\n";
    echo "POST /api/fcm-token\n";
    echo "Body: {\"fcm_token\": \"votre_token_fcm_ici\"}\n\n";
    exit;
}

echo "✅ Admin trouvé: {$admin->first_name} {$admin->last_name} (ID: {$admin->id})\n";
echo "   Token FCM: " . substr($admin->fcm_token, 0, 20) . "...\n\n";

// 2. Vérifier qu'il y a des colis et ramassages de test
$colis = Colis::where('status', 2)->first(); // Colis livré
$ramassage = Ramassage::where('statut', 'termine')->first();
$livreur = Livreur::first();

if (!$colis) {
    echo "❌ Aucun colis livré trouvé pour le test\n";
    exit;
}

if (!$ramassage) {
    echo "❌ Aucun ramassage terminé trouvé pour le test\n";
    exit;
}

if (!$livreur) {
    echo "❌ Aucun livreur trouvé pour le test\n";
    exit;
}

echo "✅ Données de test trouvées:\n";
echo "   - Colis: {$colis->code} (ID: {$colis->id})\n";
echo "   - Ramassage: {$ramassage->code_ramassage} (ID: {$ramassage->id})\n";
echo "   - Livreur: {$livreur->first_name} {$livreur->last_name} (ID: {$livreur->id})\n\n";

// 3. Tester le service Firebase
echo "=== Test du service Firebase ===\n";

try {
    $firebaseService = new ServiceAccountFirebaseService();
    echo "✅ Service Firebase initialisé\n";
    
    // Test de validation du token
    $isValid = $firebaseService->validateToken($admin->fcm_token);
    echo $isValid ? "✅ Token FCM valide\n" : "❌ Token FCM invalide\n";
    
} catch (Exception $e) {
    echo "❌ Erreur service Firebase: " . $e->getMessage() . "\n";
}

echo "\n=== Test des notifications ===\n";

// 4. Tester la notification de livraison terminée
echo "Test notification livraison terminée...\n";
try {
    $result = $firebaseService->sendDeliveryCompletedNotificationToAdmin($colis, $livreur, $admin->fcm_token);
    
    if ($result['success']) {
        echo "✅ Notification de livraison envoyée avec succès\n";
    } else {
        echo "❌ Échec notification livraison: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur notification livraison: " . $e->getMessage() . "\n";
}

// 5. Tester la notification de ramassage terminé
echo "\nTest notification ramassage terminé...\n";
try {
    $result = $firebaseService->sendRamassageCompletedNotificationToAdmin($ramassage, $livreur, $admin->fcm_token);
    
    if ($result['success']) {
        echo "✅ Notification de ramassage envoyée avec succès\n";
    } else {
        echo "❌ Échec notification ramassage: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur notification ramassage: " . $e->getMessage() . "\n";
}

echo "\n=== Résumé ===\n";
echo "Pour recevoir les notifications:\n";
echo "1. L'admin doit enregistrer son token FCM via l'API\n";
echo "2. Quand un livreur termine une livraison, l'admin recevra une notification\n";
echo "3. Quand un livreur termine un ramassage, l'admin recevra une notification\n";
echo "4. Les notifications sont envoyées via Firebase Cloud Messaging\n\n";

echo "API pour enregistrer le token FCM:\n";
echo "POST /api/fcm-token\n";
echo "Headers: Authorization: Bearer {token_admin}\n";
echo "Body: {\"fcm_token\": \"votre_token_fcm\"}\n\n";

echo "Test terminé !\n";
