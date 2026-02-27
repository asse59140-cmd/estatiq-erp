#!/bin/bash

# Script d'installation des dépendances KORE ERP
# Résolution des dépendances fantômes pour la production

echo "🔧 Installation des dépendances KORE ERP - Blindage & Production"
echo "================================================================"
echo ""

# Vérifier si Composer est installé
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé. Installation en cours..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

echo "📦 Installation des SDKs critiques..."
echo ""

# SDK Stripe pour paiements
echo "1️⃣  Installation Stripe SDK..."
composer require stripe/stripe-php:^13.0 --no-interaction --optimize-autoloader

# SDK DocuSign pour signatures électroniques
echo "2️⃣  Installation DocuSign SDK..."
composer require docusign/esign-client:^6.0 --no-interaction --optimize-autoloader

# Client HTTP Guzzle pour API IA
echo "3️⃣  Installation Guzzle HTTP..."
composer require guzzlehttp/guzzle:^7.8 --no-interaction --optimize-autoloader

# Dépendances Redis pour files d'attente
echo "4️⃣  Installation Redis..."
composer require predis/predis:^2.2 --no-interaction --optimize-autoloader

# SDK Google pour IA
echo "5️⃣  Installation Google AI SDK..."
composer require google/cloud-ai-platform:^1.0 --no-interaction --optimize-autoloader

# SDK OpenAI
echo "6️⃣  Installation OpenAI SDK..."
composer require openai-php/client:^0.8 --no-interaction --optimize-autoloader

echo ""
echo "✅ Installation des dépendances terminée !"
echo ""
echo "📋 Résumé des packages installés :"
echo "   - stripe/stripe-php : Paiements sécurisés"
echo "   - docusign/esign-client : Signatures électroniques"
echo "   - guzzlehttp/guzzle : Client HTTP robuste"
echo "   - predis/predis : Cache Redis haute performance"
echo "   - google/cloud-ai-platform : IA Google"
echo "   - openai-php/client : IA OpenAI"
echo ""
echo "🚀 KORE ERP est maintenant prêt pour la production !"

# Optimisation finale
echo ""
echo "⚡ Optimisation de l'autoloader..."
composer dump-autoload --optimize --classmap-authoritative