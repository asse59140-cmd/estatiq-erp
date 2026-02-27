#!/bin/bash

# KORE ERP - Migration de Marque Finale
# Remplacement systématique ESTATIQ → KORE ERP avec conventions strictes

echo "🏢 KORE ERP - Migration de Marque Finale"
echo "========================================"
echo ""

# Vérifier si on est dans un projet Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Ce n'est pas un projet Laravel"
    exit 1
fi

# Fonction de remplacement avec vérification
replace_with_check() {
    local pattern="$1"
    local replacement="$2"
    local file_types="$3"
    local description="$4"
    
    echo "🔧 $description"
    
    # Trouver et remplacer
    find . -type f \( $file_types \) -exec grep -l "$pattern" {} \; | while read file; do
        if [ -w "$file" ]; then
            # Sauvegarder l'original
            cp "$file" "$file.backup.$(date +%Y%m%d%H%M%S)"
            
            # Remplacer
            sed -i "s/$pattern/$replacement/g" "$file"
            echo "  ✅ $(basename "$file")"
        else
            echo "  ⚠️  $(basename "$file") - Non modifiable"
        fi
    done
    echo ""
}

# 1. Classes et Namespaces (StudlyCase)
echo "📋 Classes et Namespaces (StudlyCase)"
replace_with_check "Estatiq" "KoreErp" "-name '*.php'" "Classes PHP (StudlyCase)"
replace_with_check "ESTATIQ" "KOREERP" "-name '*.php'" "Constantes PHP (StudlyCase)"

# 2. Variables et colonnes BDD (snake_case)
echo "🗄️ Variables et colonnes BDD (snake_case)"
replace_with_check "estatiq" "kore_erp" "-name '*.php'" "Variables PHP (snake_case)"
replace_with_check "estatiq" "kore_erp" "-name '*.sql'" "Fichiers SQL (snake_case)"

# 3. URLs et slugs (kebab-case)
echo "🌐 URLs et slugs (kebab-case)"
replace_with_check "estatiq.com" "kore-erp.com" "-name '*.php' -o -name '*.js' -o -name '*.vue' -o -name '*.json' -o -name '*.md'" "Domaines (kebab-case)"
replace_with_check "estatiq" "kore-erp" "-name '*.php' -o -name '*.js' -o -name '*.vue' -o -name '*.json'" "Slugs (kebab-case)"

# 4. Interface et langues (avec espaces)
echo "🌍 Interface et langues (avec espaces)"
replace_with_check "KOREERP" "KORE ERP" "-name '*.php' -path './lang/*'" "Traductions (avec espaces)"
replace_with_check "KoreErp" "KORE ERP" "-name '*.php' -path './lang/*'" "Interface (avec espaces)"

# 5. Configuration spécifique
echo "⚙️ Configuration spécifique"

# composer.json
if [ -f "composer.json" ]; then
    sed -i 's/"name": ".*estatiq.*"/"name": "kore\/kore-erp"/' composer.json
    sed -i 's/"description": ".*Estatiq.*"/"description": "KORE ERP - Real Estate Intelligence Platform"/' composer.json
    echo "  ✅ composer.json"
fi

# package.json
if [ -f "package.json" ]; then
    sed -i 's/"name": ".*estatiq.*"/"name": "kore-erp"/' package.json
    sed -i 's/"description": ".*Estatiq.*"/"description": "KORE ERP - Real Estate Intelligence Platform"/' package.json
    echo "  ✅ package.json"
fi

# .env files
for env_file in .env .env.example .env.production .env.local; do
    if [ -f "$env_file" ]; then
        sed -i 's/APP_NAME=.*/APP_NAME="KORE ERP"/' "$env_file"
        sed -i 's/APP_URL=.*/APP_URL=https:\/\/kore-erp.com/' "$env_file"
        echo "  ✅ $env_file"
    fi
done

# 6. Docker configuration
echo "🐳 Docker Configuration"
replace_with_check "estatiq" "kore-erp" "-name 'docker-compose.yml' -o -name 'Dockerfile' -o -name '*.yml'" "Docker (kebab-case)"
replace_with_check "ESTATIQ" "KOREERP" "-name 'docker-compose.yml' -o -name 'Dockerfile'" "Docker (StudlyCase)"

# 7. Documentation
echo "📚 Documentation"
replace_with_check "ESTATIQ" "KORE ERP" "-name '*.md'" "Documentation (avec espaces)"
replace_with_check "Estatiq" "KORE ERP" "-name '*.md'" "Documentation (avec espaces)"

# 8. Noms de fichiers et répertoires
echo "📁 Fichiers et répertoires"

# Renommer les répertoires
find . -type d -name "*estatiq*" | while read dir; do
    new_dir=$(echo "$dir" | sed 's/estatiq/kore-erp/g')
    mv "$dir" "$new_dir" 2>/dev/null && echo "  📂 $dir → $new_dir"
done

# Renommer les fichiers
find . -type f -name "*estatiq*" | while read file; do
    new_file=$(echo "$file" | sed 's/estatiq/kore-erp/g')
    mv "$file" "$new_file" 2>/dev/null && echo "  📄 $file → $new_file"
done

# 9. Scripts spéciaux
echo "📝 Scripts spéciaux"

# Scripts de migration
if [ -d "scripts" ]; then
    replace_with_check "estatiq" "kore-erp" "-path './scripts/*' -name '*.sh'" "Scripts Bash"
    replace_with_check "ESTATIQ" "KORE ERP" "-path './scripts/*' -name '*.sh'" "Scripts Bash"
fi

# 10. Configuration Laravel
echo "🔧 Configuration Laravel"
replace_with_check "estatiq" "kore_erp" "-path './config/*' -name '*.php'" "Configuration (snake_case)"
replace_with_check "ESTATIQ" "KOREERP" "-path './config/*' -name '*.php'" "Configuration (StudlyCase)"

# 11. Routes
echo "🛣️ Routes"
replace_with_check "estatiq" "kore-erp" "-path './routes/*' -name '*.php'" "Routes (kebab-case)"
replace_with_check "Estatiq" "KORE ERP" "-path './routes/*' -name '*.php'" "Routes (avec espaces)"

# 12. Tests
echo "🧪 Tests"
replace_with_check "estatiq" "kore_erp" "-path './tests/*' -name '*.php'" "Tests (snake_case)"
replace_with_check "Estatiq" "KORE ERP" "-path './tests/*' -name '*.php'" "Tests (avec espaces)"

# 13. Base de données
echo "🗄️ Base de données"
replace_with_check "estatiq" "kore_erp" "-path './database/*' -name '*.php'" "Base de données (snake_case)"
replace_with_check "ESTATIQ" "KOREERP" "-path './database/*' -name '*.php'" "Base de données (StudlyCase)"

# 14. Vues et composants
echo "👁️ Vues et composants"
replace_with_check "estatiq" "kore-erp" "-path './resources/*' -name '*.php' -o -name '*.js' -o -name '*.vue'" "Vues (kebab-case)"
replace_with_check "ESTATIQ" "KORE ERP" "-path './resources/*' -name '*.php' -o -name '*.js' -o -name '*.vue'" "Vues (avec espaces)"

# 15. Cache et sessions
echo "💾 Cache et sessions"
replace_with_check "estatiq" "kore_erp" "-path './storage/*' -name '*.php'" "Cache (snake_case)"

# Nettoyage des backups après vérification
echo "🧹 Nettoyage des sauvegardes"
find . -name "*.backup.*" -type f | head -5 | while read backup; do
    echo "  🗑️  Sauvegarde créée : $(basename "$backup")"
done

echo ""
echo "✅ Migration de marque terminée !"
echo ""
echo "📋 Résumé des conventions appliquées :"
echo "   • Classes & Namespaces : KoreErp (StudlyCase)"
echo "   • Variables & BDD : kore_erp (snake_case)"
echo "   • URLs & Slugs : kore-erp (kebab-case)"
echo "   • Interface UI : KORE ERP (avec espaces)"
echo ""
echo "⚠️  Actions post-migration :"
echo "   1. Vérifier les modifications : git status"
echo "   2. Tester l'application : php artisan serve"
echo "   3. Vider les caches : php artisan cache:clear"
echo "   4. Recompiler les assets : npm run build"
echo "   5. Mettre à jour la documentation"
echo ""
echo "🚀 KORE ERP est maintenant harmonisé et prêt pour la production !"