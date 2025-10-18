<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

echo "📋 Affichage des logs récents de création de boutique...\n\n";

$logFile = storage_path('logs/laravel.log');

if (!file_exists($logFile)) {
    echo "❌ Fichier de log non trouvé: {$logFile}\n";
    exit(1);
}

// Lire les dernières lignes du fichier de log
$lines = file($logFile);
$recentLines = array_slice($lines, -50); // Dernières 50 lignes

echo "📄 Dernières 50 lignes du fichier de log:\n";
echo str_repeat("=", 80) . "\n";

foreach ($recentLines as $line) {
    // Filtrer les lignes liées aux boutiques
    if (strpos($line, 'boutique') !== false ||
        strpos($line, 'Boutique') !== false ||
        strpos($line, 'marchand') !== false ||
        strpos($line, 'Marchand') !== false) {
        echo $line;
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ Logs affichés. Recherchez les entrées liées aux boutiques ci-dessus.\n";
