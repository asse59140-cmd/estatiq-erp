#!/bin/bash

# KORE ERP - Finalisation du Blindage & Production
# Script de finalisation pour déploiement international

echo "🛡️  KORE ERP - Finalisation du Blindage & Production"
echo "===================================================="
echo ""

# Vérifier si on est dans un projet Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Ce n'est pas un projet Laravel"
    exit 1
fi

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}📋 Vérification de la configuration...${NC}"
echo ""

# 1. Vérifier la configuration de production
if [ ! -f ".env.production" ]; then
    echo -e "${YELLOW}⚠️  Configuration de production non trouvée${NC}"
    echo "   Création de la configuration par défaut..."
    cp .env.example .env.production
fi

# 2. Vérifier les variables d'environnement critiques
echo -e "${BLUE}🔧 Vérification des variables critiques...${NC}"

# Vérifier APP_KEY
if grep -q "APP_KEY=" .env.production && grep -q "APP_KEY=$" .env.production; then
    echo -e "${YELLOW}⚠️  APP_KEY non défini dans .env.production${NC}"
    echo "   Génération d'une nouvelle clé..."
    php artisan key:generate --env=production --force
fi

# Vérifier la configuration Redis
if ! grep -q "REDIS_HOST=" .env.production; then
    echo -e "${RED}❌ Configuration Redis manquante${NC}"
    exit 1
fi

# 3. Vérifier les dépendances Composer
echo -e "${BLUE}📦 Vérification des dépendances...${NC}"

if [ ! -f "composer.lock" ]; then
    echo -e "${YELLOW}⚠️  composer.lock non trouvé${NC}"
    echo "   Installation des dépendances..."
    composer install --no-dev --optimize-autoloader
fi

# 4. Vérifier les migrations
echo -e "${BLUE}🗄️  Vérification des migrations...${NC}"

# Vérifier si les tables de base existent
if ! php artisan migrate:status --env=production | grep -q "Ran"; then
    echo -e "${YELLOW}⚠️  Aucune migration exécutée${NC}"
    echo "   Exécution des migrations..."
    php artisan migrate --env=production --force
fi

# 5. Vérifier les indexes multi-tenant
echo -e "${BLUE}🔍 Vérification des indexes multi-tenant...${NC}"

php artisan migrate --path=database/migrations/2024_01_01_000000_add_composite_indexes_for_multitenant.php --env=production --force

# 6. Optimiser la configuration Laravel
echo -e "${BLUE}⚡ Optimisation de Laravel...${NC}"

echo "   Cache de configuration..."
php artisan config:cache --env=production

echo "   Cache des routes..."
php artisan route:cache --env=production

echo "   Cache des vues..."
php artisan view:cache --env=production

echo "   Cache des événements..."
php artisan event:cache --env=production

# 7. Vérifier les files d'attente
echo -e "${BLUE}📋 Configuration des files d'attente...${NC}"

# Créer les tables de queue si nécessaire
php artisan queue:table --env=production --force
php artisan queue:failed-table --env=production --force
php artisan migrate --env=production --force

# 8. Vérifier Horizon
echo -e "${BLUE}📊 Configuration de Horizon...${NC}"

if ! php artisan horizon:status --env=production 2>/dev/null | grep -q "running"; then
    echo -e "${YELLOW}⚠️  Horizon n'est pas en cours d'exécution${NC}"
    echo "   Démarrage de Horizon..."
    php artisan horizon --env=production &
fi

# 9. Vérifier les permissions
echo -e "${BLUE}🔒 Vérification des permissions...${NC}"

# Permissions sur le dossier storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 public

# 10. Vérification finale
echo -e "${BLUE}✅ Vérification finale...${NC}"

# Tester la connexion Redis
if php artisan redis:connection-test --env=production 2>/dev/null; then
    echo -e "${GREEN}✅ Connexion Redis OK${NC}"
else
    echo -e "${RED}❌ Connexion Redis échouée${NC}"
    exit 1
fi

# Tester la connexion MySQL
if php artisan db:connection-test --env=production 2>/dev/null; then
    echo -e "${GREEN}✅ Connexion MySQL OK${NC}"
else
    echo -e "${RED}❌ Connexion MySQL échouée${NC}"
    exit 1
fi

# 11. Sécurité finale
echo -e "${BLUE}🛡️  Application des dernières sécurités...${NC}"

# Désactiver le mode debug
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env.production

# Forcer HTTPS
sed -i 's/SESSION_SECURE_COOKIE=false/SESSION_SECURE_COOKIE=true/' .env.production
sed -i 's/SESSION_HTTP_ONLY=false/SESSION_HTTP_ONLY=true/' .env.production
sed -i 's/SESSION_SAME_SITE=.*/SESSION_SAME_SITE=strict/' .env.production

# 12. Nettoyage
echo -e "${BLUE}🧹 Nettoyage...${NC}"

# Supprimer les logs de développement
rm -f storage/logs/*.log 2>/dev/null
rm -f storage/logs/laravel-*.log 2>/dev/null

# Nettoyer le cache
php artisan cache:clear --env=production
php artisan config:clear --env=production
php artisan route:clear --env=production
php artisan view:clear --env=production

# Recréer les caches
php artisan config:cache --env=production
php artisan route:cache --env=production
php artisan view:cache --env=production

echo ""
echo -e "${GREEN}🎉 Blindage finalisé avec succès !${NC}"
echo ""
echo -e "${BLUE}📋 Résumé des vérifications:${NC}"
echo "   ✅ Configuration de production"
echo "   ✅ Clés d'encryption"
echo "   ✅ Connexion Redis/MySQL"
echo "   ✅ Indexes multi-tenant"
echo "   ✅ Files d'attente configurées"
echo "   ✅ Horizon monitoring"
echo "   ✅ Permissions sécurisées"
echo "   ✅ Cache optimisé"
echo "   ✅ Mode production activé"
echo ""
echo -e "${YELLOW}⚠️  Actions manuelles recommandées:${NC}"
echo "   1. Vérifier les credentials Stripe dans .env.production"
echo "   2. Vérifier les credentials DocuSign dans .env.production"
echo "   3. Vérifier les API keys IA dans .env.production"
echo "   4. Tester le déploiement sur un environnement de staging"
echo "   5. Configurer les backups automatiques"
echo "   6. Configurer la surveillance (monitoring)"
echo ""
echo -e "${GREEN}🚀 KORE ERP est maintenant blindé et prêt pour la production !${NC}"
echo ""
echo -e "${BLUE}Commandes utiles:${NC}"
echo "   php artisan serve --env=production    # Démarrer le serveur"
echo "   php artisan horizon --env=production  # Démarrer Horizon"
echo "   php artisan queue:work --env=production # Démarrer les workers"
echo "   php artisan schedule:run --env=production # Exécuter le scheduler"