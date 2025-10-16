# Système OTP (One-Time Password) - Documentation

## Vue d'ensemble

Le système OTP a été implémenté pour sécuriser le processus d'inscription en envoyant un code de vérification par email avant de créer le compte utilisateur.

## Architecture du système

### 🔧 Composants implémentés

#### **1. Modèle `EmailVerification`**
- **Table** : `email_verifications`
- **Fonctionnalités** :
  - Génération d'OTP de 6 chiffres
  - Expiration automatique (10 minutes)
  - Stockage temporaire des données utilisateur
  - Validation et vérification des codes

#### **2. Contrôleur `AuthController`**
- **Méthodes ajoutées** :
  - `showVerifyOTP()` - Affichage du formulaire de vérification
  - `verifyOTP()` - Vérification du code et création du compte
  - `resendOTP()` - Renvoi d'un nouveau code
  - `sendOTPEmail()` - Envoi de l'email avec le code

#### **3. Vue `verify-otp.blade.php`**
- **Fonctionnalités** :
  - Formulaire de saisie du code OTP
  - Validation côté client
  - Auto-submit quand 6 chiffres sont saisis
  - Bouton de renvoi avec timer
  - Design responsive et moderne

#### **4. Routes ajoutées**
```php
GET  /verify-otp     - Formulaire de vérification
POST /verify-otp     - Traitement de la vérification
POST /resend-otp     - Renvoi du code
```

## Flux d'inscription avec OTP

### **Étape 1 : Inscription initiale**
```
POST /register
```
1. **Validation** des données utilisateur
2. **Stockage temporaire** des données dans `email_verifications`
3. **Génération** d'un OTP de 6 chiffres
4. **Envoi** de l'email avec le code
5. **Redirection** vers le formulaire de vérification

### **Étape 2 : Vérification OTP**
```
GET /verify-otp
POST /verify-otp
```
1. **Affichage** du formulaire de vérification
2. **Saisie** du code OTP (6 chiffres)
3. **Validation** du code (expiration, format, unicité)
4. **Création** du compte utilisateur
5. **Envoi** de l'email de bienvenue
6. **Redirection** vers la connexion

### **Étape 3 : Renvoi d'OTP (optionnel)**
```
POST /resend-otp
```
1. **Génération** d'un nouveau code
2. **Mise à jour** de l'expiration
3. **Envoi** du nouvel email

## Sécurité implémentée

### **🔒 Mesures de sécurité**

#### **1. Expiration des codes**
- **Durée** : 10 minutes
- **Vérification** : Automatique lors de la validation
- **Nettoyage** : Suppression des codes expirés

#### **2. Unicité des codes**
- **Un seul code actif** par email
- **Suppression** des anciens codes lors de la création d'un nouveau
- **Validation** de l'état de vérification

#### **3. Validation stricte**
- **Format** : Exactement 6 chiffres
- **Type** : Uniquement des chiffres
- **Expiration** : Vérification de la date
- **État** : Code non encore vérifié

#### **4. Logs de sécurité**
- **Tentatives** de vérification
- **Envois** d'OTP
- **Erreurs** et échecs
- **Adresses IP** et timestamps

## Configuration

### **Variables d'environnement**
```env
# Configuration Mailjet (déjà configurée)
MAILJET_APIKEY_PUBLIC=63f92592baf083fb4b37043e9c16c1b3
MAILJET_APIKEY_PRIVATE=c6dfe57a01fd28090c54a719dc2ff644
MAILJET_SENDER_EMAIL=disbonjour2000@gmail.com
MAILJET_SENDER_NAME="MOYOO fleet"
```

### **Paramètres configurables**
```php
// Dans EmailVerification::createVerification()
'expires_at' => Carbon::now()->addMinutes(10) // Durée d'expiration

// Dans EmailVerification::generateOTP()
return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // Longueur du code
```

## Tests automatisés

### **✅ Tests implémentés (10 tests)**

#### **1. Tests d'affichage**
- ✅ Affichage du formulaire de vérification
- ✅ Redirection sans email en session

#### **2. Tests d'inscription**
- ✅ Envoi d'OTP lors de l'inscription
- ✅ Stockage temporaire des données

#### **3. Tests de vérification**
- ✅ Vérification avec code valide
- ✅ Vérification avec code invalide
- ✅ Vérification avec code expiré

#### **4. Tests de renvoi**
- ✅ Renvoi d'OTP
- ✅ Génération de nouveau code

#### **5. Tests de modèle**
- ✅ Génération d'OTP
- ✅ Création de vérification
- ✅ Validation d'OTP

### **Exécution des tests**
```bash
# Tous les tests OTP
php artisan test --filter=OTPTest

# Test spécifique
php artisan test --filter=test_otp_verification_with_valid_code
```

## Utilisation

### **Pour l'utilisateur**

#### **1. Inscription**
1. Remplir le formulaire d'inscription
2. Cliquer sur "S'inscrire"
3. Recevoir l'email avec le code OTP

#### **2. Vérification**
1. Aller sur la page de vérification
2. Saisir le code de 6 chiffres
3. Le formulaire se soumet automatiquement
4. Recevoir l'email de bienvenue

#### **3. Renvoi (si nécessaire)**
1. Cliquer sur "Renvoyer le code"
2. Attendre le nouveau email
3. Saisir le nouveau code

### **Pour le développeur**

#### **1. Personnalisation**
```php
// Modifier la durée d'expiration
'expires_at' => Carbon::now()->addMinutes(15) // 15 minutes

// Modifier la longueur du code
return str_pad(random_int(0, 999999), 8, '0', STR_PAD_LEFT); // 8 chiffres
```

#### **2. Monitoring**
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log | grep OTP

# Vérifier les vérifications en cours
php artisan tinker
>>> App\Models\EmailVerification::notExpired()->notVerified()->get();
```

## Avantages du système

### **✅ Sécurité renforcée**
- **Vérification d'email** obligatoire
- **Protection** contre les inscriptions frauduleuses
- **Expiration** automatique des codes
- **Logs** détaillés des opérations

### **✅ Expérience utilisateur**
- **Interface intuitive** et moderne
- **Auto-submit** pour faciliter la saisie
- **Messages clairs** et informatifs
- **Possibilité de renvoi** du code

### **✅ Maintenabilité**
- **Code modulaire** et réutilisable
- **Tests complets** et automatisés
- **Documentation** détaillée
- **Configuration** flexible

## Résolution de problèmes

### **Problèmes courants**

#### **1. Code expiré**
- **Cause** : Dépassement des 10 minutes
- **Solution** : Utiliser le bouton "Renvoyer le code"

#### **2. Email non reçu**
- **Cause** : Problème de configuration Mailjet
- **Solution** : Vérifier les logs et la configuration

#### **3. Code invalide**
- **Cause** : Saisie incorrecte ou code déjà utilisé
- **Solution** : Vérifier la saisie ou demander un nouveau code

### **Debug**
```bash
# Vérifier les vérifications en cours
php artisan tinker
>>> App\Models\EmailVerification::all();

# Vérifier les logs
tail -f storage/logs/laravel.log
```

Le système OTP est maintenant **entièrement fonctionnel** et **sécurisé** ! 🎯
