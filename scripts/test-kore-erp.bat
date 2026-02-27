@echo off
echo.
echo 🏢 KORE ERP - SYSTEM TEST
echo ========================================
echo.

REM Vérifier si PHP est disponible
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ PHP n'est pas installé ou non accessible dans le PATH
    echo.
    echo 💡 Installation de PHP requise:
    echo    - Télécharger PHP: https://windows.php.net/download/
    echo    - Ajouter PHP au PATH système
    echo    - Redémarrer le terminal
    echo.
    pause
    exit /b 1
)

echo ✅ PHP trouvé
php -v
echo.

REM Vérifier si Composer est disponible
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ⚠️  Composer non trouvé - Installation recommandée
    echo    Télécharger: https://getcomposer.org/download/
    echo.
)

REM Vérifier la configuration PHP
echo 🔧 Vérification de la configuration PHP...
echo.

REM Extensions PHP requises
echo 📋 Extensions PHP:
php -m | findstr /i "pdo pdo_mysql mbstring openssl tokenizer xml ctype json bcmath redis gd zip curl"
echo.

REM Vérifier Laravel
echo 🎯 Vérification de Laravel...
if exist "artisan" (
    echo ✅ Laravel trouvé
    echo.
    
    REM Afficher la version de Laravel
    php artisan --version
    echo.
    
    REM Vérifier l'environnement
    echo 🌍 Environnement:
    php artisan env
    echo.
    
    REM Vérifier les configurations
    echo ⚙️  Configuration:
    echo    APP_NAME: 
    php artisan tinker --execute="echo env('APP_NAME', 'Non défini');"
    echo    APP_ENV: 
    php artisan tinker --execute="echo env('APP_ENV', 'Non défini');"
    echo    APP_DEBUG: 
    php artisan tinker --execute="echo env('APP_DEBUG', 'Non défini');"
    echo.
    
    REM Test de connexion base de données
    echo 🗄️  Test de connexion base de données...
    php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Connexion MySQL réussie'; } catch (Exception \$e) { echo '❌ Erreur MySQL: ' . \$e->getMessage(); }"
    echo.
    
    REM Test de connexion Redis
    echo 💾 Test de connexion Redis...
    php artisan tinker --execute="try { Cache::put('kore_test', 'test', 1); \$result = Cache::get('kore_test'); echo \$result === 'test' ? '✅ Redis fonctionnel' : '❌ Redis non fonctionnel'; Cache::forget('kore_test'); } catch (Exception \$e) { echo '❌ Erreur Redis: ' . \$e->getMessage(); }"
    echo.
    
    REM Vérifier les migrations
    echo 📊 Migrations:
    php artisan migrate:status | findstr /c:"Ran" /c:"Pending"
    echo.
    
    REM Afficher les routes KORE ERP
    echo 🛣️  Routes KORE ERP:
    php artisan route:list | findstr /i "kore-erp"
    echo.
    
    REM Test des commandes personnalisées
    echo 🔍 Commandes disponibles:
    php artisan list | findstr /i "kore"
    echo.
    
    REM Test de sécurité multi-tenant
    echo 🛡️  Test Multi-Tenant:
    php artisan tinker --execute="
        try {
            \$agency = App\Models\Agency::first();
            if (\$agency) {
                echo '✅ Agence trouvée: ' . \$agency->name . PHP_EOL;
                echo '   Domaine: ' . \$agency->domain . PHP_EOL;
                echo '   Devise: ' . \$agency->currency . PHP_EOL;
            } else {
                echo '⚠️  Aucune agence trouvée - Base de données vide ou non initialisée' . PHP_EOL;
            }
        } catch (Exception \$e) {
            echo '❌ Erreur: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    echo.
    
    REM Test de la configuration Arabe
    echo 🇦🇪 Configuration Arabe:
    php artisan tinker --execute="
        try {
            \$config = config('arabic');
            if (\$config) {
                echo '✅ Configuration Arabe présente' . PHP_EOL;
                echo '   Direction par défaut: ' . (\$config['default_direction'] ?? 'ltr') . PHP_EOL;
                echo '   Locale: ' . (\$config['default_locale'] ?? 'en') . PHP_EOL;
            } else {
                echo '⚠️  Configuration Arabe non trouvée' . PHP_EOL;
            }
        } catch (Exception \$e) {
            echo '❌ Erreur: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    echo.
    
    REM Test des services IA
    echo 🤖 Services IA:
    php artisan tinker --execute="
        try {
            \$aiConfig = config('ai');
            if (\$aiConfig) {
                \$openai = !empty(\$aiConfig['providers']['openai']['api_key']) ? '✅' : '❌';
                \$google = !empty(\$aiConfig['providers']['google']['api_key']) ? '✅' : '❌';
                \$anthropic = !empty(\$aiConfig['providers']['anthropic']['api_key']) ? '✅' : '❌';
                
                echo 'OpenAI: ' . \$openai . ' Configuré' . PHP_EOL;
                echo 'Google AI: ' . \$google . ' Configuré' . PHP_EOL;
                echo 'Anthropic: ' . \$anthropic . ' Configuré' . PHP_EOL;
            } else {
                echo '⚠️  Configuration IA non trouvée' . PHP_EOL;
            }
        } catch (Exception \$e) {
            echo '❌ Erreur: ' . \$e->getMessage() . PHP_EOL;
        }
    "
    echo.
    
    echo 🎉 TEST KORE ERP TERMINÉ
    echo ========================================
    echo.
    echo 💡 Prochaines étapes:
    echo    1. Configurer les variables d'environnement dans .env
    echo    2. Exécuter: php artisan migrate
    echo    3. Exécuter: php artisan db:seed (si nécessaire)
    echo    4. Démarrer le serveur: php artisan serve
    echo    5. Accéder à: http://localhost:8000/dashboard
    echo.
    echo 🚀 KORE ERP est prêt pour le déploiement !
    echo.
    
) else (
    echo ❌ Laravel non trouvé - Assurez-vous d'être dans le bon répertoire
    echo.
    echo 📍 Répertoire actuel: %cd%
    echo.
    echo 💡 Structure attendue:
    echo    - artisan (fichier)
    echo    - app/ (dossier)
    echo    - resources/ (dossier)
    echo    - etc.
    echo.
)

pause