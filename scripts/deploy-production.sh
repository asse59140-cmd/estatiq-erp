#!/bin/bash

# KORE ERP - Script de Déploiement Production
# Scellement final et mise en production

set -e

echo "🏢 KORE ERP - PROCÉDURE DE SCELLEMENT FINAL"
echo "============================================="
echo ""

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Fonctions utilitaires
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

log_header() {
    echo -e "${PURPLE}\n🚀 $1${NC}"
    echo -e "${PURPLE}$(printf '=%.0s' {1..50})${NC}"
}

# Vérification des prérequis
check_prerequisites() {
    log_header "VÉRIFICATION DES PRÉREQUIS"
    
    # Vérifier Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker n'est pas installé"
        exit 1
    fi
    
    # Vérifier Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose n'est pas installé"
        exit 1
    fi
    
    # Vérifier les fichiers nécessaires
    required_files=("Dockerfile" "docker-compose.yml" ".env")
    for file in "${required_files[@]}"; do
        if [ ! -f "$file" ]; then
            log_error "Fichier manquant: $file"
            exit 1
        fi
    done
    
    log_success "Tous les prérequis sont satisfaits"
}

# Configuration de l'environnement
setup_environment() {
    log_header "CONFIGURATION DE L'ENVIRONNEMENT"
    
    # Créer le fichier .env s'il n'existe pas
    if [ ! -f ".env" ]; then
        cp .env.example .env
        log_warning "Fichier .env créé à partir de .env.example"
    fi
    
    # Générer la clé d'application si nécessaire
    if grep -q "APP_KEY=" .env && grep -q "APP_KEY=$" .env; then
        log_info "Génération de la clé d'application..."
        php artisan key:generate --force
    fi
    
    # Configuration Redis
    if ! grep -q "REDIS_SESSION_DB=" .env; then
        echo "REDIS_SESSION_DB=2" >> .env
        echo "REDIS_CACHE_DB=1" >> .env
        echo "REDIS_QUEUE_DB=3" >> .env
        log_info "Configuration Redis ajoutée"
    fi
    
    log_success "Environnement configuré"
}

# Construction des images Docker
build_docker_images() {
    log_header "CONSTRUCTION DES IMAGES DOCKER"
    
    log_info "Construction de l'image KORE ERP..."
    docker-compose build --no-cache --pull
    
    log_success "Images Docker construites avec succès"
}

# Démarrage des services
deploy_services() {
    log_header "DÉMARRAGE DES SERVICES"
    
    # Arrêter les services existants
    docker-compose down --remove-orphans || true
    
    # Démarrer les nouveaux services
    docker-compose up -d
    
    # Attendre que les services soient prêts
    log_info "Attente du démarrage des services..."
    sleep 30
    
    # Vérifier l'état des services
    if docker-compose ps | grep -q "Up"; then
        log_success "Services démarrés avec succès"
    else
        log_error "Erreur lors du démarrage des services"
        docker-compose logs
        exit 1
    fi
}

# Exécution des migrations
run_migrations() {
    log_header "EXÉCUTION DES MIGRATIONS"
    
    log_info "Exécution des migrations de base de données..."
    docker-compose exec -T kore-erp php artisan migrate --force
    
    log_info "Exécution des seeders..."
    docker-compose exec -T kore-erp php artisan db:seed --force
    
    log_success "Migrations terminées"
}

# Optimisation de l'application
optimize_application() {
    log_header "OPTIMISATION DE L'APPLICATION"
    
    log_info "Mise en cache des configurations..."
    docker-compose exec -T kore-erp php artisan config:cache
    
    log_info "Mise en cache des routes..."
    docker-compose exec -T kore-erp php artisan route:cache
    
    log_info "Mise en cache des vues..."
    docker-compose exec -T kore-erp php artisan view:cache
    
    log_info "Mise en cache des événements..."
    docker-compose exec -T kore-erp php artisan event:cache
    
    log_success "Optimisation terminée"
}

# Tests de santé
health_check() {
    log_header "TESTS DE SANTÉ"
    
    # Test de la base de données
    if docker-compose exec -T kore-erp php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" | grep -q "OK"; then
        log_success "Connexion base de données OK"
    else
        log_error "Erreur connexion base de données"
        exit 1
    fi
    
    # Test Redis
    if docker-compose exec -T kore-erp php artisan tinker --execute="Cache::put('test', 'ok', 1); echo Cache::get('test');" | grep -q "ok"; then
        log_success "Connexion Redis OK"
    else
        log_error "Erreur connexion Redis"
        exit 1
    fi
    
    # Test des queues
    if docker-compose exec -T kore-erp php artisan queue:restart; then
        log_success "Service de queue OK"
    else
        log_error "Erreur service de queue"
        exit 1
    fi
    
    log_success "Tous les tests de santé passés"
}

# Test final du système
final_system_test() {
    log_header "TEST FINAL DU SYSTÈME"
    
    log_info "Exécution du test complet KORE ERP..."
    docker-compose exec -T kore-erp php artisan kore:system-test --full --demo
    
    log_success "Test système terminé avec succès"
}

# Affichage des informations de déploiement
show_deployment_info() {
    log_header "INFORMATIONS DE DÉPLOIEMENT"
    
    echo -e "${BLUE}KORE ERP - Real Estate Intelligence Platform${NC}"
    echo -e "${BLUE}Version: 1.0.0${NC}"
    echo -e "${BLUE}Statut: PRODUCTION READY${NC}"
    echo ""
    
    echo -e "${GREEN}Services actifs:${NC}"
    docker-compose ps
    echo ""
    
    echo -e "${GREEN}URLs d'accès:${NC}"
    echo "  • Application: http://localhost"
    echo "  • API: http://localhost/api"
    echo "  • Health Check: http://localhost/health"
    echo ""
    
    echo -e "${GREEN}Commandes utiles:${NC}"
    echo "  • Logs: docker-compose logs -f"
    echo "  • Shell: docker-compose exec kore-erp bash"
    echo "  • Artisan: docker-compose exec kore-erp php artisan"
    echo "  • Arrêt: docker-compose down"
    echo ""
    
    echo -e "${PURPLE}KORE ERP est maintenant opérationnel et prêt à dominer le marché!${NC}"
}

# Nettoyage en cas d'erreur
cleanup() {
    if [ $? -ne 0 ]; then
        log_error "Erreur détectée - Nettoyage en cours..."
        docker-compose down --remove-orphans || true
        exit 1
    fi
}

# Configuration du trap pour le nettoyage
trap cleanup EXIT

# Fonction principale
main() {
    log_header "DÉBUT DU SCELLEMENT KORE ERP"
    
    check_prerequisites
    setup_environment
    build_docker_images
    deploy_services
    run_migrations
    optimize_application
    health_check
    final_system_test
    show_deployment_info
    
    log_success "🎉 SCELLEMENT KORE ERP TERMINÉ AVEC SUCCÈS!"
    log_success "🏢 La plateforme est maintenant prête pour la production!"
    log_success "🚀 Prêt à dominer le marché Middle East!"
}

# Exécution du script
main "$@"