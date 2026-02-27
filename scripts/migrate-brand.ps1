# Script de migration de marque : ESTATIQ → KORE ERP
# KORE ERP - Blindage & Production

Write-Host "🏢 Migration de marque : ESTATIQ → KORE ERP" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si on est dans un projet Laravel
if (!(Test-Path "artisan")) {
    Write-Host "❌ Ce n'est pas un projet Laravel" -ForegroundColor Red
    exit 1
}

# Fonction pour remplacer dans les fichiers
function Replace-InFiles {
    param(
        [string]$Pattern,
        [string]$Replacement,
        [string]$FileTypes,
        [string]$Description
    )
    
    Write-Host "🔧 $Description" -ForegroundColor Yellow
    
    Get-ChildItem -Recurse -File $FileTypes | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        if ($content -match $Pattern) {
            $newContent = $content -replace $Pattern, $Replacement
            if ($newContent -ne $content) {
                Set-Content -Path $_.FullName -Value $newContent -NoNewline
                Write-Host "  ✅ $($_.Name)" -ForegroundColor Green
            }
        }
    }
    Write-Host ""
}

# 1. Remplacements dans les fichiers PHP
Write-Host "📄 Fichiers PHP..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "*.php" -Description "Fichiers PHP"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "*.php" -Description "Fichiers PHP (capitalisé)"
Replace-InFiles -Pattern "estatiq" -Replacement "kore-erp" -FileTypes "*.php" -Description "Fichiers PHP (minuscule)"

# 2. Fichiers de configuration
Write-Host "⚙️ Fichiers de configuration..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "config\*.php" -Description "Configuration"
Replace-InFiles -Pattern "estatiq.com" -Replacement "kore-erp.com" -FileTypes "config\*.php" -Description "Domaines"

# 3. Fichiers de langue
Write-Host "🌐 Fichiers de langue..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "lang\*.php" -Description "Fichiers de langue"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "lang\*.php" -Description "Fichiers de langue (capitalisé)"

# 4. Fichiers JSON
Write-Host "📋 Fichiers JSON..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "*.json" -Description "Fichiers JSON"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "*.json" -Description "Fichiers JSON (capitalisé)"

# 5. Fichiers Markdown
Write-Host "📝 Fichiers Markdown..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "*.md" -Description "Documentation"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "*.md" -Description "Documentation (capitalisé)"

# 6. Fichiers JavaScript/Vue
Write-Host "🎨 Fichiers Frontend..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "*.js", "*.vue", "*.ts" -Description "JavaScript/Vue"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "*.js", "*.vue", "*.ts" -Description "JavaScript/Vue (capitalisé)"

# 7. Fichiers CSS
Write-Host "🎨 Fichiers CSS..." -ForegroundColor Blue
Replace-InFiles -Pattern "estatiq" -Replacement "kore-erp" -FileTypes "*.css", "*.scss", "*.sass" -Description "Fichiers CSS"

# 8. Fichiers Blade
Write-Host "🔤 Fichiers Blade..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "*.blade.php" -Description "Templates Blade"
Replace-InFiles -Pattern "Estatiq" -Replacement "KORE ERP" -FileTypes "*.blade.php" -Description "Templates Blade (capitalisé)"

# 9. Migrations SQL
Write-Host "🗄️ Migrations SQL..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "database\migrations\*.php" -Description "Migrations"

# 10. Seeders
Write-Host "🌱 Seeders..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "database\seeders\*.php" -Description "Seeders"

# 11. Tests
Write-Host "🧪 Tests..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "tests\*.php" -Description "Tests"

# 12. Fichiers de configuration Docker
Write-Host "🐳 Docker..." -ForegroundColor Blue
Replace-InFiles -Pattern "ESTATIQ" -Replacement "KORE ERP" -FileTypes "Dockerfile", "docker-compose.yml", "*.yml" -Description "Docker"

# 13. README et documentation
Write-Host "📚 Documentation..." -ForegroundColor Blue
if (Test-Path "README.md") {
    $content = Get-Content "README.md" -Raw
    $newContent = $content -replace "ESTATIQ", "KORE ERP" -replace "Estatiq", "KORE ERP" -replace "estatiq", "kore-erp"
    Set-Content "README.md" -Value $newContent -NoNewline
    Write-Host "  ✅ README.md" -ForegroundColor Green
}

# 14. composer.json
Write-Host "📦 Composer..." -ForegroundColor Blue
if (Test-Path "composer.json") {
    $content = Get-Content "composer.json" -Raw
    $newContent = $content -replace '"name": ".*estatiq.*"', '"name": "kore/kore-erp"' -replace '"description": ".*Estatiq.*"', '"description": "KORE ERP - Real Estate Intelligence Platform"'
    Set-Content "composer.json" -Value $newContent -NoNewline
    Write-Host "  ✅ composer.json" -ForegroundColor Green
}

# 15. package.json
Write-Host "📦 Package JSON..." -ForegroundColor Blue
if (Test-Path "package.json") {
    $content = Get-Content "package.json" -Raw
    $newContent = $content -replace '"name": ".*estatiq.*"', '"name": "kore-erp"' -replace '"description": ".*Estatiq.*"', '"description": "KORE ERP - Real Estate Intelligence Platform"'
    Set-Content "package.json" -Value $newContent -NoNewline
    Write-Host "  ✅ package.json" -ForegroundColor Green
}

# 16. .env.example
Write-Host "🔧 Environnement..." -ForegroundColor Blue
if (Test-Path ".env.example") {
    $content = Get-Content ".env.example" -Raw
    $newContent = $content -replace "APP_NAME=.*", 'APP_NAME="KORE ERP"' -replace "APP_URL=.*", "APP_URL=https://kore-erp.com"
    Set-Content ".env.example" -Value $newContent -NoNewline
    Write-Host "  ✅ .env.example" -ForegroundColor Green
}

Write-Host ""
Write-Host "✅ Migration terminée !" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Actions recommandées :" -ForegroundColor Cyan
Write-Host "   1. Vérifier les modifications avec : git status" -ForegroundColor White
Write-Host "   2. Tester l'application : php artisan serve" -ForegroundColor White
Write-Host "   3. Vider le cache : php artisan cache:clear" -ForegroundColor White
Write-Host "   4. Recompiler les assets : npm run build" -ForegroundColor White
Write-Host "   5. Mettre à jour la documentation" -ForegroundColor White
Write-Host ""
Write-Host "🚀 KORE ERP est maintenant prêt !" -ForegroundColor Green