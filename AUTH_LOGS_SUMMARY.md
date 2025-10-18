# 📋 Résumé des Logs d'Authentification

## 🔐 **Logs d'Inscription (registerUser)**

### 1. **Début du processus d'inscription**
```php
Log::info('Début du processus d\'inscription', [
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'request_data' => $request->except(['password', 'password_confirmation'])
]);
```

### 2. **Validation des données réussie**
```php
Log::info('Validation des données d\'inscription réussie', [
    'email' => $request->email,
    'first_name' => $request->first_name,
    'last_name' => $request->last_name,
    'mobile' => $request->mobile,
    'ip' => $request->ip()
]);
```

### 3. **Création de la vérification OTP**
```php
Log::info('Création de la vérification OTP', [
    'email' => $userData['email'],
    'ip' => $request->ip()
]);
```

### 4. **Envoi de l'OTP par email**
```php
Log::info('Envoi de l\'OTP par email', [
    'email' => $userData['email'],
    'first_name' => $userData['first_name'],
    'ip' => $request->ip()
]);
```

### 5. **OTP envoyé avec succès**
```php
Log::info('OTP envoyé avec succès - Inscription en attente de vérification', [
    'email' => $userData['email'],
    'verification_id' => $verification->id,
    'ip' => $request->ip()
]);
```

## ✅ **Logs de Vérification OTP (verifyOTP)**

### 6. **Début de la vérification OTP**
```php
Log::info('Début de la vérification OTP', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

### 7. **Validation OTP réussie**
```php
Log::info('Validation OTP réussie - Création du compte en cours', [
    'email' => $email,
    'verification_id' => $verification->id,
    'ip' => $request->ip()
]);
```

### 8. **Données utilisateur préparées**
```php
Log::info('Données utilisateur préparées pour la création', [
    'email' => $userData['email'],
    'first_name' => $userData['first_name'],
    'last_name' => $userData['last_name'],
    'mobile' => $userData['mobile'],
    'ip' => $request->ip()
]);
```

### 9. **Envoi de l'email de bienvenue**
```php
Log::info('Envoi de l\'email de bienvenue', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip()
]);
```

### 10. **Inscription complétée avec succès**
```php
Log::info('Inscription complétée avec succès - Compte créé', [
    'user_id' => $user->id,
    'email' => $user->email,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'mobile' => $user->mobile,
    'created_at' => $user->created_at,
    'ip' => $request->ip()
]);
```

## 🔑 **Logs de Connexion (loginUser)**

### 11. **Tentative de connexion**
```php
Log::info('Tentative de connexion', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'remember' => $request->boolean('remember')
]);
```

### 12. **Validation des données de connexion**
```php
Log::info('Validation des données de connexion réussie', [
    'email' => $request->email,
    'ip' => $request->ip()
]);
```

### 13. **Connexion réussie**
```php
Log::info('Connexion réussie', [
    'user_id' => $user->id,
    'email' => $user->email,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'entreprise_id' => $user->entreprise_id,
    'remember_me' => $request->boolean('remember'),
    'session_id' => $request->session()->getId(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'login_time' => now()->toDateTimeString()
]);
```

### 14. **Vérification des accès utilisateur**
```php
Log::info('Vérification des accès utilisateur', [
    'user_id' => $user->id,
    'email' => $user->email,
    'entreprise_id' => $user->entreprise_id,
    'has_entreprise' => !is_null($user->entreprise_id),
    'ip' => $request->ip()
]);
```

## ⚠️ **Logs d'Erreurs**

### 15. **Tentative de connexion bloquée (trop de tentatives)**
```php
Log::warning('Tentative de connexion bloquée - Trop de tentatives', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'seconds_remaining' => $seconds
]);
```

### 16. **Tentative de connexion échouée**
```php
Log::warning('Tentative de connexion échouée', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'attempts_remaining' => 5 - RateLimiter::attempts($key),
    'reason' => 'Identifiants incorrects'
]);
```

### 17. **Vérification OTP échouée - Code invalide**
```php
Log::warning('Vérification OTP échouée - Code invalide ou expiré', [
    'email' => $email,
    'ip' => $request->ip()
]);
```

### 18. **Vérification OTP échouée - Code incorrect**
```php
Log::warning('Vérification OTP échouée - Code incorrect', [
    'email' => $email,
    'otp_provided' => $otp,
    'ip' => $request->ip()
]);
```

## 🚪 **Logs de Déconnexion (logout)**

### 19. **Déconnexion utilisateur**
```php
Log::info('Déconnexion utilisateur', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip()
]);
```

## 📊 **Informations Loggées**

### **Données Utilisateur :**
- ID utilisateur
- Email
- Prénom et nom
- Numéro de téléphone
- ID entreprise
- Date de création

### **Données de Session :**
- ID de session
- Temps de connexion
- Option "Se souvenir de moi"

### **Données de Sécurité :**
- Adresse IP
- User Agent
- Nombre de tentatives restantes
- Raison des échecs

### **Données de Vérification :**
- ID de vérification OTP
- Code OTP fourni
- Statut de validation

## 🎯 **Utilisation des Logs**

1. **Surveillance de la sécurité** : Détection des tentatives de connexion suspectes
2. **Débogage** : Traçage complet du processus d'inscription et de connexion
3. **Audit** : Historique des actions utilisateur
4. **Support** : Aide au diagnostic des problèmes d'authentification

## 📁 **Fichier de Log**

Tous les logs sont enregistrés dans : `storage/logs/laravel.log`

## 🔍 **Test des Logs**

Utilisez le script `test_auth_logs.php` pour vérifier que tous les logs sont bien générés.
