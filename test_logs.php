<?php

// Script de test pour vérifier les logs
echo "=== TEST DES LOGS ===\n";

// Vérifier si le fichier de log existe
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "Fichier de log trouvé: {$logFile}\n";

    // Lire les dernières lignes du log
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20); // 20 dernières lignes

    echo "\n=== DERNIÈRES 20 LIGNES DU LOG ===\n";
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "Fichier de log non trouvé: {$logFile}\n";
}

echo "\n=== FIN DU TEST ===\n";
