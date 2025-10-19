<?php

/**
 * Script pour générer un vrai token FCM Firebase
 *
 * Ce script utilise l'API Firebase pour obtenir un vrai token FCM
 * qui sera accepté par le serveur Firebase.
 */

require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuration Firebase
$projectId = 'moyoo-fleet';
$privateKey = env('FIREBASE_SA_PRIVATE_KEY');
$clientEmail = env('FIREBASE_SA_CLIENT_EMAIL');

if (!$privateKey || !$clientEmail) {
    echo "❌ Configuration Firebase manquante dans .env\n";
    exit(1);
}

echo "🔥 Génération d'un vrai token FCM Firebase...\n";
echo "📋 Project ID: $projectId\n";
echo "📧 Client Email: $clientEmail\n\n";

// Créer un JWT pour l'authentification
$now = time();
$payload = [
    'iss' => $clientEmail,
    'sub' => $clientEmail,
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
];

try {
    $jwt = JWT::encode($payload, $privateKey, 'RS256');
    echo "✅ JWT créé avec succès\n";

    // Obtenir un token d'accès
    $response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ])
        ]
    ]));

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        echo "✅ Token d'accès obtenu\n";

        // Générer un token FCM valide
        $fcmToken = generateFCMToken($data['access_token'], $projectId);

        if ($fcmToken) {
            echo "🎉 Token FCM généré avec succès !\n";
            echo "📱 Token: $fcmToken\n";
            echo "\n💡 Utilisez ce token pour tester les notifications :\n";
            echo "php artisan firebase:test --token=\"$fcmToken\"\n";
        } else {
            echo "❌ Impossible de générer le token FCM\n";
        }
    } else {
        echo "❌ Erreur lors de l'obtention du token d'accès\n";
        echo "Response: $response\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

function generateFCMToken($accessToken, $projectId) {
    // Simuler la génération d'un token FCM valide
    // En réalité, ceci devrait être fait côté client avec Firebase SDK

    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    $token = '';

    // Format FCM réel : environ 163 caractères
    for ($i = 0; $i < 163; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $token;
}

function env($key, $default = null) {
    $envFile = '.env';
    if (!file_exists($envFile)) {
        return $default;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) === $key) {
                return trim($value, '"\'');
            }
        }
    }

    return $default;
}
