# 📚 Guide Swagger - Documentation API

## 🎯 Vue d'ensemble

Ce projet utilise **Swagger/OpenAPI** pour générer automatiquement une documentation interactive de l'API. La documentation est accessible via l'interface web et se met à jour automatiquement.

## 🌐 Accès à la Documentation

### URL de la Documentation
```
http://127.0.0.1:8000/api/documentation
```

### Interface Swagger UI
- **Interface interactive** : Testez les endpoints directement depuis le navigateur
- **Authentification intégrée** : Utilisez le bouton "Authorize" pour ajouter votre token JWT
- **Exemples de requêtes** : Chaque endpoint contient des exemples de requêtes et réponses

## 🔧 Configuration

### Fichiers de Configuration
- **`config/l5-swagger.php`** : Configuration principale de Swagger
- **`app/Http/Controllers/SwaggerController.php`** : Informations générales de l'API

### Génération de la Documentation
```bash
# Génération manuelle
php artisan l5-swagger:generate

# Ou utiliser le script automatisé
./scripts/update-swagger.sh
```

## 📝 Ajout d'Annotations Swagger

### Structure des Annotations

#### 1. Informations Générales de l'API
```php
/**
 * @OA\Info(
 *     title="API Livreur - MOYOO Delivery",
 *     version="1.0.0",
 *     description="API pour l'application mobile des livreurs"
 * )
 */
```

#### 2. Endpoint avec Authentification
```php
/**
 * @OA\Post(
 *     path="/api/livreur/login",
 *     summary="Connexion du livreur",
 *     description="Authentification d'un livreur",
 *     tags={"Authentification Livreur"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"mobile","password"},
 *             @OA\Property(property="mobile", type="string", example="1234567890"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Connexion réussie",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Connexion réussie")
 *         )
 *     )
 * )
 */
public function login(Request $request)
```

#### 3. Endpoint Protégé
```php
/**
 * @OA\Get(
 *     path="/api/livreur/profile",
 *     summary="Obtenir le profil",
 *     tags={"Profil Livreur"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Succès")
 * )
 */
public function profile()
```

### Types de Réponses

#### Réponse de Succès
```php
@OA\Response(
    response=200,
    description="Opération réussie",
    @OA\JsonContent(
        @OA\Property(property="success", type="boolean", example=true),
        @OA\Property(property="message", type="string", example="Opération réussie"),
        @OA\Property(property="data", type="object")
    )
)
```

#### Réponse d'Erreur
```php
@OA\Response(
    response=400,
    description="Erreur de validation",
    @OA\JsonContent(
        @OA\Property(property="success", type="boolean", example=false),
        @OA\Property(property="message", type="string", example="Données invalides"),
        @OA\Property(property="errors", type="object")
    )
)
```

## 🏷️ Tags et Organisation

### Tags Disponibles
- **Authentification Livreur** : Endpoints de connexion/déconnexion
- **Profil Livreur** : Gestion du profil utilisateur
- **Colis** : Gestion des colis et livraisons
- **Géolocalisation** : Mise à jour de position
- **Statistiques** : Rapports et statistiques

### Ajout d'un Nouveau Tag
```php
/**
 * @OA\Tag(
 *     name="Nouveau Tag",
 *     description="Description du nouveau tag"
 * )
 */
```

## 🔐 Authentification JWT

### Configuration de l'Authentification
```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
```

### Utilisation dans les Endpoints
```php
security={{"bearerAuth":{}}}
```

## 🚀 Workflow de Développement

### 1. Créer un Nouvel Endpoint
```php
/**
 * @OA\Post(
 *     path="/api/livreur/nouveau-endpoint",
 *     summary="Description courte",
 *     description="Description détaillée",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}}, // Si protégé
 *     @OA\RequestBody(...),
 *     @OA\Response(...)
 * )
 */
public function nouveauEndpoint(Request $request)
{
    // Implémentation
}
```

### 2. Mettre à Jour la Documentation
```bash
# Option 1: Commande manuelle
php artisan l5-swagger:generate

# Option 2: Script automatisé
./scripts/update-swagger.sh

# Option 3: Automatique après commit (si hook activé)
git commit -m "Ajout nouvel endpoint"
```

### 3. Tester dans Swagger UI
1. Aller sur `http://127.0.0.1:8000/api/documentation`
2. Trouver le nouvel endpoint
3. Cliquer sur "Try it out"
4. Remplir les paramètres
5. Cliquer sur "Execute"

## 📋 Bonnes Pratiques

### 1. Annotations Complètes
- **Toujours** inclure `summary` et `description`
- **Détailler** les paramètres avec `@OA\Property`
- **Documenter** tous les codes de réponse possibles

### 2. Exemples Réalistes
- Utiliser des **exemples concrets** et réalistes
- Inclure des **données de test** valides
- Montrer les **formats de réponse** attendus

### 3. Organisation
- **Grouper** les endpoints par tags logiques
- **Nommer** clairement les endpoints
- **Maintenir** la cohérence dans la structure

### 4. Validation
- **Tester** chaque endpoint dans Swagger UI
- **Vérifier** que les exemples fonctionnent
- **Valider** les schémas de réponse

## 🔄 Mise à Jour Automatique

### Hook Git (Optionnel)
Un hook Git est configuré pour mettre à jour automatiquement la documentation après chaque commit :

```bash
# Le hook s'exécute automatiquement après :
git commit -m "Ajout nouvel endpoint"
```

### Script Manuel
```bash
# Pour une mise à jour manuelle
./scripts/update-swagger.sh
```

## 🐛 Dépannage

### Problèmes Courants

#### 1. Documentation non mise à jour
```bash
# Vérifier la génération
php artisan l5-swagger:generate

# Vérifier les logs
tail -f storage/logs/laravel.log
```

#### 2. Erreurs d'annotations
- Vérifier la syntaxe des annotations `@OA\`
- S'assurer que tous les `@OA\Property` sont correctement fermés
- Vérifier les types de données (string, integer, boolean, etc.)

#### 3. Authentification ne fonctionne pas
- Vérifier que le token JWT est valide
- S'assurer que le header `Authorization: Bearer {token}` est correct
- Vérifier que le livreur est actif dans la base de données

### Logs et Debug
```bash
# Activer le mode debug
APP_DEBUG=true

# Vérifier les logs
tail -f storage/logs/laravel.log

# Tester un endpoint spécifique
curl -X POST http://127.0.0.1:8000/api/livreur/login \
  -H "Content-Type: application/json" \
  -d '{"mobile":"1234567890","password":"password123"}'
```

## 📚 Ressources Utiles

- **Documentation Swagger** : https://swagger.io/docs/
- **OpenAPI Specification** : https://swagger.io/specification/
- **L5-Swagger Laravel** : https://github.com/DarkaOnLine/L5-Swagger
- **Interface Swagger UI** : http://127.0.0.1:8000/api/documentation

## 🎯 Prochaines Étapes

1. **Ajouter** des annotations pour tous les nouveaux endpoints
2. **Tester** chaque endpoint dans Swagger UI
3. **Maintenir** la documentation à jour
4. **Former** l'équipe sur l'utilisation de Swagger
5. **Intégrer** Swagger dans le pipeline CI/CD
