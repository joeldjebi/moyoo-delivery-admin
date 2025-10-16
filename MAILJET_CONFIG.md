# Configuration Mailjet

## Variables d'environnement requises

### Configuration minimale (obligatoire)
```env
# Clés API Mailjet (obligatoires)
MAILJET_APIKEY_PUBLIC=your_mailjet_public_key
MAILJET_APIKEY_PRIVATE=your_mailjet_private_key

# Expéditeur par défaut (obligatoire)
MAILJET_SENDER_EMAIL=noreply@moyoo.com
MAILJET_SENDER_NAME="MOYOO Admin Delivery"
```

### Configuration complète (optionnelle)
```env
# Configuration expéditeur
MAILJET_SENDER_EMAIL=noreply@moyoo.com
MAILJET_SENDER_NAME="MOYOO Admin Delivery"

# Configuration réponse
MAILJET_REPLY_TO_EMAIL=support@moyoo.com
MAILJET_REPLY_TO_NAME="Support MOYOO"

# Configuration API
MAILJET_API_URL=https://api.mailjet.com/v3.1/send
MAILJET_TIMEOUT=30
MAILJET_VERIFY_SSL=true

# Templates Mailjet (si utilisés)
MAILJET_WELCOME_TEMPLATE_ID=
MAILJET_PASSWORD_RESET_TEMPLATE_ID=
MAILJET_NOTIFICATION_TEMPLATE_ID=

# Configuration logs
MAILJET_LOGGING_ENABLED=true
MAILJET_LOG_SUCCESS=true
MAILJET_LOG_ERRORS=true

# Configuration sécurité
MAILJET_RATE_LIMIT_ENABLED=true
MAILJET_RATE_LIMIT_MAX_ATTEMPTS=10
MAILJET_RATE_LIMIT_DECAY_MINUTES=60
MAILJET_TOKEN_EXPIRY_MINUTES=60
```

> **Note :** Un fichier `config/mailjet.env.example` est disponible avec toutes les variables documentées.

## Comment obtenir vos clés API Mailjet

1. Connectez-vous à votre compte [Mailjet](https://app.mailjet.com/)
2. Allez dans **Account Settings** > **API Key Management**
3. Copiez votre **API Key** (clé publique) et **Secret Key** (clé privée)
4. Ajoutez-les à votre fichier `.env`

## Fonctionnalités implémentées

### ✅ Envoi d'email de bienvenue
- Automatiquement envoyé lors de l'inscription d'un nouvel utilisateur
- Template HTML professionnel avec bouton de connexion

### ✅ Réinitialisation de mot de passe
- Formulaire de demande de réinitialisation
- Email avec lien sécurisé
- Token de validation temporaire

### ✅ Sécurité
- Rate limiting sur les tentatives de connexion
- Validation stricte des données
- Logs détaillés des opérations
- Gestion d'erreurs robuste

## Utilisation

### Envoi d'email de bienvenue
```php
// Automatiquement appelé lors de l'inscription
$this->sendWelcomeEmail($user);
```

### Envoi d'email de réinitialisation
```php
// Via le formulaire de mot de passe oublié
POST /password-forget
```

### Réinitialisation du mot de passe
```php
// Via le lien reçu par email
POST /reset-password
```

## Routes ajoutées

- `GET /password-forget` - Formulaire de mot de passe oublié
- `POST /password-forget` - Envoi de l'email de réinitialisation
- `GET /reset-password` - Formulaire de réinitialisation
- `POST /reset-password` - Traitement de la réinitialisation

## Logs

Tous les envois d'emails sont loggés avec :
- Succès/échec de l'envoi
- Adresse email du destinataire
- Sujet de l'email
- Erreurs détaillées en cas d'échec
