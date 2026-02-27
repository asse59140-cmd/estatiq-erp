#!/bin/bash

# Script de migration de marque : ESTATIQ → KORE ERP
# KORE ERP - Blindage & Production

echo "🏢 Migration de marque : ESTATIQ → KORE ERP"
echo "=============================================="
echo ""

# Vérifier si on est dans un projet Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Ce n'est pas un projet Laravel"
    exit 1
fi

# Fonction pour remplacer dans les fichiers
replace_in_files() {
    local pattern="$1"
    local replacement="$2"
    local file_types="$3"
    local description="$4"
    
    echo "🔧 $description"
    
    # Rechercher et remplacer
    find . -type f \( $file_types \) -exec grep -l "$pattern" {} \; | while read file; do
        if [ -w "$file" ]; then
            sed -i "s/$pattern/$replacement/g" "$file"
            echo "  ✅ $(basename "$file")"
        else
            echo "  ⚠️  $(basename "$file") - Non modifiable"
        fi
    done
    echo ""
}

# 1. Remplacements dans les fichiers PHP
echo "📄 Fichiers PHP..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php'" "Fichiers PHP"
replace_in_files "Estatiq" "KORE ERP" "-name '*.php'" "Fichiers PHP (capitalisé)"
replace_in_files "estatiq" "kore-erp" "-name '*.php'" "Fichiers PHP (minuscule)"

# 2. Fichiers de configuration
echo "⚙️  Fichiers de configuration..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php' -path './config/*'" "Configuration"
replace_in_files "estatiq.com" "kore-erp.com" "-name '*.php' -path './config/*'" "Domaines"

# 3. Fichiers de langue
echo "🌐 Fichiers de langue..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php' -path './lang/*'" "Fichiers de langue"
replace_in_files "Estatiq" "KORE ERP" "-name '*.php' -path './lang/*'" "Fichiers de langue (capitalisé)"

# 4. Fichiers JSON
echo "📋 Fichiers JSON..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.json'" "Fichiers JSON"
replace_in_files "Estatiq" "KORE ERP" "-name '*.json'" "Fichiers JSON (capitalisé)"

# 5. Fichiers Markdown
echo "📝 Fichiers Markdown..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.md'" "Documentation"
replace_in_files "Estatiq" "KORE ERP" "-name '*.md'" "Documentation (capitalisé)"

# 6. Fichiers JavaScript/Vue
echo "🎨 Fichiers Frontend..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.js' -o -name '*.vue' -o -name '*.ts'" "JavaScript/Vue"
replace_in_files "Estatiq" "KORE ERP" "-name '*.js' -o -name '*.vue' -o -name '*.ts'" "JavaScript/Vue (capitalisé)"

# 7. Fichiers CSS
echo "🎨 Fichiers CSS..."
replace_in_files "estatiq" "kore-erp" "-name '*.css' -o -name '*.scss' -o -name '*.sass'" "Fichiers CSS"

# 8. Fichiers Blade
echo "🔤 Fichiers Blade..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.blade.php'" "Templates Blade"
replace_in_files "Estatiq" "KORE ERP" "-name '*.blade.php'" "Templates Blade (capitalisé)"

# 9. Migrations SQL
echo "🗄️  Migrations SQL..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php' -path './database/migrations/*'" "Migrations"

# 10. Seeders
echo "🌱 Seeders..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php' -path './database/seeders/*'" "Seeders"

# 11. Tests
echo "🧪 Tests..."
replace_in_files "ESTATIQ" "KORE ERP" "-name '*.php' -path './tests/*'" "Tests"

# 12. Fichiers de configuration Docker
echo "🐳 Docker..."
replace_in_files "ESTATIQ" "KORE ERP" "-name 'Dockerfile' -o -name 'docker-compose.yml' -o -name '*.yml'" "Docker"

# 13. README et documentation
echo "📚 Documentation..."
if [ -f "README.md" ]; then
    sed -i 's/ESTATIQ/KORE ERP/g' README.md
    sed -i 's/Estatiq/KORE ERP/g' README.md
    sed -i 's/estatiq/kore-erp/g' README.md
    echo "  ✅ README.md"
fi

# 14. composer.json
echo "📦 Composer..."
if [ -f "composer.json" ]; then
    sed -i 's/"name": ".*estatiq.*"/"name": "kore\/kore-erp"/g' composer.json
    sed -i 's/"description": ".*Estatiq.*"/"description": "KORE ERP - Real Estate Intelligence Platform"/g' composer.json
    echo "  ✅ composer.json"
fi

# 15. package.json
echo "📦 Package JSON..."
if [ -f "package.json" ]; then
    sed -i 's/"name": ".*estatiq.*"/"name": "kore-erp"/g' package.json
    sed -i 's/"description": ".*Estatiq.*"/"description": "KORE ERP - Real Estate Intelligence Platform"/g' package.json
    echo "  ✅ package.json"
fi

# 16. .env.example
echo "🔧 Environnement..."
if [ -f ".env.example" ]; then
    sed -i 's/APP_NAME=.*/APP_NAME="KORE ERP"/g' .env.example
    sed -i 's/APP_URL=.*/APP_URL=https:\/\/kore-erp.com/g' .env.example
    echo "  ✅ .env.example"
fi

echo ""
echo "✅ Migration terminée !"
echo ""
echo "📋 Actions recommandées :"
echo "   1. Vérifier les modifications avec : git status"
echo "   2. Tester l'application : php artisan serve"
echo "   3. Vider le cache : php artisan cache:clear"
echo "   4. Recompiler les assets : npm run build"
echo "   5. Mettre à jour la documentation"
echo ""
echo "🚀 KORE ERP est maintenant prêt !"