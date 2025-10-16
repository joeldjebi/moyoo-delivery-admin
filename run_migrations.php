<?php

// Script pour exécuter les migrations
echo "=== EXÉCUTION DES MIGRATIONS ===\n";

// Vérifier si nous sommes dans le bon répertoire
if (!file_exists('artisan')) {
    echo "ERREUR: Fichier artisan non trouvé. Assurez-vous d'être dans le répertoire Laravel.\n";
    exit(1);
}

// Exécuter les migrations
echo "Exécution des migrations...\n";
$output = shell_exec('php artisan migrate 2>&1');
echo "Résultat:\n";
echo $output;

echo "\n=== FIN EXÉCUTION ===\n";
