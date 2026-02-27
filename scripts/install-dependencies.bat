@echo off
REM Script d'installation des dépendances KORE ERP - Windows
REM Résolution des dépendances fantômes pour la production

echo "🔧 Installation des dépendances KORE ERP - Blindage & Production"
echo "================================================================"
echo.

REM Vérifier si Composer est installé
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo "❌ Composer n'est pas installé."
    echo "Veuillez installer Composer depuis https://getcomposer.org/download/"
    pause
    exit /b 1
)

echo "📦 Installation des SDKs critiques..."
echo.

REM SDK Stripe pour paiements
echo "1️⃣  Installation Stripe SDK..."
call composer require stripe/stripe-php:^13.0 --no-interaction --optimize-autoloader

REM SDK DocuSign pour signatures électroniques
echo "2️⃣  Installation DocuSign SDK..."
call composer require docusign/esign-client:^6.0 --no-interaction --optimize-autoloader

REM Client HTTP Guzzle pour API IA
echo "3️⃣  Installation Guzzle HTTP..."
call composer require guzzlehttp/guzzle:^7.8 --no-interaction --optimize-autoloader

REM Dépendances Redis pour files d'attente
echo "4️⃣  Installation Redis..."
call composer require predis/predis:^2.2 --no-interaction --optimize-autoloader

REM SDK Google pour IA
echo "5️⃣  Installation Google AI SDK..."
call composer require google/cloud-ai-platform:^1.0 --no-interaction --optimize-autoloader

REM SDK OpenAI
echo "6️⃣  Installation OpenAI SDK..."
call composer require openai-php/client:^0.8 --no-interaction --optimize-autoloader

echo.
echo "✅ Installation des dépendances terminée !"
echo.
echo "📋 Résumé des packages installés :"
echo "   - stripe/stripe-php : Paiements sécurisés"
echo "   - docusign/esign-client : Signatures électroniques"
echo "   - guzzlehttp/guzzle : Client HTTP robuste"
echo "   - predis/predis : Cache Redis haute performance"
echo "   - google/cloud-ai-platform : IA Google"
echo "   - openai-php/client : IA OpenAI"
echo.
echo "🚀 KORE ERP est maintenant prêt pour la production !"

REM Optimisation finale
echo.
echo "⚡ Optimisation de l'autoloader..."
call composer dump-autoload --optimize --classmap-authoritative

echo.
echo "Appuyez sur une touche pour continuer..."
pause >nul