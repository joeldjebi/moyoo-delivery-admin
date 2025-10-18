# Configuration de l'URL Swagger

## Problème résolu ✅

L'URL de base du Swagger a été configurée pour correspondre à votre serveur qui démarre sur `http://192.168.1.5:8000/`.

## Modifications effectuées

### 1. Configuration des fichiers
- **`config/l5-swagger.php`** : URL de base mise à jour vers `http://192.168.1.5:8000`
- **`config/app.php`** : URL de l'application mise à jour vers `http://192.168.1.5:8000`

### 2. Annotations Swagger mises à jour
- **`SwaggerController.php`** : Annotation `@OA\Server` mise à jour
- **`LivreurAuthController.php`** : Exemples d'URLs mises à jour
- **`LivreurRamassageController.php`** : Exemples d'URLs mises à jour
- **`LivreurDeliveryController.php`** : Exemples d'URLs mises à jour

### 3. Documentation régénérée
- La documentation Swagger a été régénérée avec la nouvelle URL
- L'URL `http://192.168.1.5:8000` est maintenant disponible dans la section des serveurs

## Accès à la documentation

Votre documentation Swagger est maintenant accessible à :
**http://192.168.1.5:8000/api/documentation**

## Script de configuration automatique

Un script a été créé pour faciliter la configuration de l'URL Swagger :

```bash
# Utiliser l'URL par défaut (192.168.1.9:8000)
./scripts/configure-swagger-url.sh

# Utiliser une URL personnalisée
./scripts/configure-swagger-url.sh http://votre-ip:port
```

## Variables d'environnement

Si vous créez un fichier `.env`, ajoutez ces variables :

```env
APP_URL=http://192.168.1.5:8000
L5_SWAGGER_BASE_PATH=http://192.168.1.5:8000
L5_SWAGGER_CONST_HOST=http://192.168.1.5:8000
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_USE_ABSOLUTE_PATH=true
```

## Commandes utiles

```bash
# Régénérer la documentation Swagger
php artisan l5-swagger:generate

# Vider le cache de configuration
php artisan config:clear

# Voir l'état des routes
php artisan route:list | grep api
```

## Vérification

Pour vérifier que tout fonctionne :

1. Démarrez votre serveur : `php artisan serve --host=192.168.1.9 --port=8000`
2. Accédez à : `http://192.168.1.5:8000/api/documentation`
3. Vérifiez que l'URL de base dans Swagger correspond à votre serveur

## Dépannage

Si l'URL ne correspond toujours pas :

1. Vérifiez que le serveur démarre bien sur `192.168.1.9:8000`
2. Régénérez la documentation : `php artisan l5-swagger:generate`
3. Videz le cache : `php artisan config:clear`
4. Redémarrez le serveur

## Notes importantes

- L'URL doit être accessible depuis votre réseau local
- Assurez-vous que le port 8000 n'est pas bloqué par un firewall
- Pour la production, utilisez une URL HTTPS sécurisée
