#!/bin/bash

# Script pour mettre à jour la configuration Firebase dans .env
echo "🔧 Mise à jour de la configuration Firebase..."

# Vérifier si le fichier .env existe
if [ ! -f .env ]; then
    echo "❌ Fichier .env non trouvé !"
    exit 1
fi

# Créer une sauvegarde
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Sauvegarde créée : .env.backup.$(date +%Y%m%d_%H%M%S)"

# Ajouter ou mettre à jour les variables Firebase
echo "" >> .env
echo "# Configuration Firebase Service Account Key" >> .env
echo "FIREBASE_PROJECT_ID=moyoo-fleet" >> .env
echo "FIREBASE_SA_TYPE=service_account" >> .env
echo "FIREBASE_SA_PROJECT_ID=moyoo-fleet" >> .env
echo "FIREBASE_SA_PRIVATE_KEY_ID=3f1efe0e75ae34254332dc191aea01541455a233" >> .env
echo "FIREBASE_SA_PRIVATE_KEY=\"-----BEGIN PRIVATE KEY-----\\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC89mrsqf952EKy\\nkJDelGuf96oytPPYyHVCszQ7WzKSf575hUNbiIRBHA39pZIG6ZCjaJUpx2Mt+LZm\\nUAsGVISj2dQlCxtiKZNp5p4xejr9A3dT8FrlianvqICPEBQWwdoN4e/a6Naf2a2+\\nBhvPobOphuCaxxEEJ+w44ItfC6ITdBfGHHP1Nn+N3TfCd+yOWJn184e3eLZWdzsz\\nKj8khOj709znQp7SHyblvwEmx+WxCSZulFdWa/qtmPK4MyBI2lGNascIW5OKjUts\\nM8HIXAmSM3scWIEjCPWzCwDK/USVEKISwA4y9YWxIDyWXdmaXWt1CsbsRCebX9ZR\\nJTEJ+ZO3AgMBAAECggEAWRzShwKSlykMJz6y+yy+ZqW7D4ezmY/LcOWnI7jZ7Cmg\\nVKbqgY+rYzMyS+SZrYXXbqCi+51qoeLxTeXAlT8lgEn5WHDY/J2DxgT6pHWyvOA2\\nFZE7pJUb3Yg2/IDGIIdR6Isc/L0ifib/XyZtVik5W6Deak+nsDCNn7MRNwT67bXY\\nGY0JavwzeMcQw2sRtsbTtLmOC01Szg67YZjlC4BfcR1ymMdy52LPOBik3pC1BY3g\\nVBWU9KnzK1lD7kpJgyhK2SmYfGroZ1iAy7oOBFT47m8qhwqqnKgx+PZJa2gaDiuk\\nZdXOZSzUNCaO3rFRR+Xn5qOrmXhEDFpScgtnEyvmvQKBgQD8V+YtA/Uh/W87ePT5\\nzAGYdPFPsUC0NiyAIsdJk8dPdYfPGoW+W6celhjh0jfwjtvmuP/OP6jlLHFEejxl\\nx2NuvZ7i8SDtHDNXxkUzNin8Laqh3fgjiTihp+LQ19CRHp33acyW8gaiLJavZEn9\\nvmzVGzQ1Mj4n8yUQMY0SDISCrQKBgQC/s2Ywz1bjq8i51zcTEHYJ2DO/zioTSRPM\\nTW0a09m96HGesoaVhOVr8c2ZhBXvZCTPNZMipXNj2fQ2K6gMrIvQJGAHJPz1n4hh\\nlk+voTErAWCdMJmhE3TBBbeOLz0sFAKzMiIX5S0ESR7vpmUVJaUbBjZAxnhGi++O\\npyfkn3pgcwKBgAw+h6CvjHl1vqv4For4ZytqoTrosucLqeUdyuW7EfS9EzXtZ4fx\\nEo7dYZ+zf5tgkzMCzwbG9/8GxQg5liyqHB0HfmosoRhgQe2EZV8yxZ7C6ICqMJwo\\n0GKnrs3LawdfoPKcY5z/aWr9FuKzzxNM5iMBCut54KI3nhjHDr1NdjCRAoGBAJGG\\nh+Iv1B87bkKd/UIssd7hUM+fm1NOm4fxkwzVnCtNhMtbbU2eOGeMhW6v1dMIa+Ud\\nH5gij4lSkNB6rbUJW43jvz5NNvbjZ63lZJLIREIIZqaNmWKtGWnahDCc7cxDWJVe\\nToFjSZxCQgjdgUjfbgoo2hCsWev8GRxrCp7E7iq/AoGAIa/P0xiAzwVPzMJdS/Wc\\nacBK9G21CSgwGHwiynoNFx57019fulDu2O8+qRtnmar8SFfOMt7OVrvbcrLqZF0+\\nxzjkfUoMDQgeJ7Z3JVewjfMxg17qtwQN1blGQ6Hl7kJAeqMSZMoQDz3NsEFGmOVx\\neeaUg1uFxzwT9EjRSXnYSUg=\\n-----END PRIVATE KEY-----\\n\"" >> .env
echo "FIREBASE_SA_CLIENT_EMAIL=firebase-adminsdk-fbsvc@moyoo-fleet.iam.gserviceaccount.com" >> .env
echo "FIREBASE_SA_CLIENT_ID=109980633917965152945" >> .env
echo "FIREBASE_SA_AUTH_URI=https://accounts.google.com/o/oauth2/auth" >> .env
echo "FIREBASE_SA_TOKEN_URI=https://oauth2.googleapis.com/token" >> .env
echo "FIREBASE_SA_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs" >> .env
echo "FIREBASE_SA_CLIENT_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40moyoo-fleet.iam.gserviceaccount.com" >> .env

echo "✅ Configuration Firebase ajoutée au fichier .env"
echo "🔄 Redémarrage du serveur Laravel..."
echo ""
echo "📋 Prochaines étapes :"
echo "1. Redémarrez votre serveur Laravel"
echo "2. Testez avec : php artisan firebase:test --token=YOUR_FCM_TOKEN"
echo "3. Vérifiez les logs : tail -f storage/logs/laravel.log"
