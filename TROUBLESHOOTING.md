# Guide de dépannage - MOYOO Admin Delivery

## Problèmes résolus

### ❌ Erreur HTTP 500 - "Call to a member function make() on true"

**Symptôme :**
```
Error
HTTP 500 Internal Server Error
Call to a member function make() on true
Error
in /Users/macbookpro/Documents/MOYOO/admin-delivery/config/mailjet-test.php (line 17)
```

**Cause :**
Le fichier `config/mailjet-test.php` était accessible via le serveur web et causait une erreur car il tentait d'initialiser Laravel de manière incorrecte.

**Solution :**
1. ✅ Suppression du fichier `config/mailjet-test.php`
2. ✅ Nettoyage du cache de configuration : `php artisan config:clear`
3. ✅ Nettoyage du cache des routes : `php artisan route:clear`
4. ✅ Ajout de règles `.gitignore` pour éviter les fichiers de test dans le web

**Prévention :**
- Les fichiers de test ne doivent jamais être placés dans le dossier `config/`
- Utiliser le dossier `tests/` pour les tests unitaires
- Ajouter des règles `.gitignore` appropriées

## Tests de configuration

### ✅ Test de la configuration Mailjet

Un test unitaire a été créé dans `tests/Feature/MailjetTest.php` pour vérifier :

1. **Configuration de base :**
   - Clés API présentes
   - Email et nom expéditeur configurés
   - Templates disponibles

2. **Configuration API :**
   - URL API correcte
   - Timeout configuré
   - SSL activé

3. **Configuration de sécurité :**
   - Rate limiting configuré
   - Expiration des tokens

4. **Templates d'emails :**
   - Sujets corrects
   - Contenu approprié

### Exécution des tests

```bash
# Tous les tests
php artisan test

# Tests Mailjet uniquement
php artisan test --filter=MailjetTest

# Tests avec détails
php artisan test --filter=MailjetTest --verbose
```

## Configuration Mailjet

### Variables d'environnement requises

```env
# Configuration minimale
MAILJET_APIKEY_PUBLIC=63f92592baf083fb4b37043e9c16c1b3
MAILJET_APIKEY_PRIVATE=c6dfe57a01fd28090c54a719dc2ff644
MAILJET_SENDER_EMAIL=disbonjour2000@gmail.com
MAILJET_SENDER_NAME="MOYOO fleet"
```

### Vérification de la configuration

```bash
# Vérifier la syntaxe PHP
php -l config/mailjet.php
php -l app/Http/Controllers/AuthController.php

# Vérifier les routes
php artisan route:list --name=auth

# Nettoyer les caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## Fonctionnalités disponibles

### ✅ Authentification
- Connexion avec rate limiting
- Inscription avec email de bienvenue
- Réinitialisation de mot de passe
- Déconnexion sécurisée

### ✅ Emails automatiques
- Email de bienvenue lors de l'inscription
- Email de réinitialisation de mot de passe
- Templates HTML professionnels

### ✅ Sécurité
- Rate limiting sur les tentatives de connexion
- Validation stricte des données
- Logs détaillés des opérations
- Tokens de sécurité temporaires

## Support

En cas de problème :

1. Vérifiez les logs : `storage/logs/laravel.log`
2. Exécutez les tests : `php artisan test --filter=MailjetTest`
3. Vérifiez la configuration : `php artisan config:show mailjet`
4. Consultez la documentation : `MAILJET_CONFIG.md`
