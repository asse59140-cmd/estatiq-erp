# 🔒 Isolation Multi-Tenant - Documentation KORE ERP

## Vue d'ensemble

Le trait `BelongsToAgency` garantit l'isolation absolue entre agences dans KORE ERP. Il applique automatiquement un filtre `WHERE agency_id = ?` sur toutes les requêtes SQL.

## Installation

### 1. Appliquer le trait aux modèles

```bash
# Appliquer à tous les modèles par défaut
php artisan kore:apply-agency-trait

# Appliquer à des modèles spécifiques
php artisan kore:apply-agency-trait --models="Building,Unit,Invoice"

# Appliquer à tous les modèles du dossier
php artisan kore:apply-agency-trait --all

# Simulation sans modification
php artisan kore:apply-agency-trait --dry-run
```

### 2. Vérifier l'application

```bash
# Vérifier quels modèles ont le trait
php artisan kore:check-agency-trait
```

## Utilisation

### Dans vos modèles

Une fois le trait appliqué, vos modèles sont automatiquement protégés :

```php
// Cette requête sera automatiquement filtrée par agency_id
$units = Unit::where('status', 'available')->get();
// Résultat : SELECT * FROM units WHERE agency_id = 1 AND status = 'available'

// Les relations sont aussi protégées
$building = Building::with('units')->first();
// Résultat : SELECT * FROM units WHERE agency_id = 1 AND building_id = ?
```

### Accès administrateur

Pour les super-administrateurs qui peuvent voir toutes les agences :

```php
// Vérifier si l'utilisateur est super-admin
if (Auth::user()->isSuperAdmin()) {
    // Le scope n'est pas appliqué automatiquement
    $allUnits = Unit::all(); // Voit toutes les agences
}
```

### Requêtes spécifiques

```php
// Forcer une agence spécifique (utile pour les rapports)
$units = Unit::forAgency(2)->get();

// Ignorer complètement le scope (admin système)
$allData = Unit::withoutAgency()->get();
```

## Sécurité

### Protection automatique

- ✅ Toutes les requêtes SELECT sont filtrées
- ✅ Toutes les requêtes UPDATE sont filtrées
- ✅ Toutes les requêtes DELETE sont filtrées
- ✅ Les relations Eloquent sont protégées
- ✅ Les requêtes avec jointures sont protégées

### Journalisation des tentatives d'accès

Les tentatives d'accès inter-agences sont automatiquement journalisées :

```
[TENTATIVE ACCÈS INTER-AGENCE] 
Utilisateur: user_id=123, agency_id=1
Tentative d'accès: model=Unit, agency_id=2
Action: UPDATE
```

### Exceptions levées

- `Exception` : Accès inter-agence détecté
- `ModelNotFoundException` : Enregistrement non trouvé dans l'agence

## Migration des données existantes

### Ajouter la colonne agency_id

```php
Schema::table('your_table', function (Blueprint $table) {
    $table->unsignedBigInteger('agency_id')->nullable()->after('id');
    $table->index('agency_id');
    
    // Clé étrangère (optionnelle)
    $table->foreign('agency_id')->references('id')->on('agencies');
});
```

### Rétrofit des données existantes

```php
// Script de migration des données
$defaultAgency = Agency::first();

YourModel::whereNull('agency_id')->update([
    'agency_id' => $defaultAgency->id
]);

// Rendre la colonne obligatoire
Schema::table('your_table', function (Blueprint $table) {
    $table->unsignedBigInteger('agency_id')->nullable(false)->change();
});
```

## Tests

### Test d'isolation

```php
public function test_agency_isolation()
{
    $agency1 = Agency::factory()->create();
    $agency2 = Agency::factory()->create();
    
    $user1 = User::factory()->create(['agency_id' => $agency1->id]);
    $user2 = User::factory()->create(['agency_id' => $agency2->id]);
    
    // Créer des données pour chaque agence
    Unit::factory()->count(3)->create(['agency_id' => $agency1->id]);
    Unit::factory()->count(2)->create(['agency_id' => $agency2->id]);
    
    // User1 ne voit que les données de son agence
    Auth::login($user1);
    $units = Unit::all();
    $this->assertCount(3, $units);
    
    // User2 ne voit que les données de son agence
    Auth::login($user2);
    $units = Unit::all();
    $this->assertCount(2, $units);
}
```

## Configuration avancée

### Variables d'environnement CLI

Pour les commandes artisan qui doivent accéder à une agence spécifique :

```bash
CLI_AGENCY_ID=1 php artisan your:command
```

### Super-administrateurs

Définir les super-administrateurs dans votre modèle User :

```php
public function isSuperAdmin(): bool
{
    return $this->role === 'super_admin' || $this->email === 'admin@kore-erp.com';
}
```

## Dépannage

### Problèmes courants

1. **"agency_id column not found"**
   - Ajoutez la colonne dans votre migration
   - Exécutez `php artisan migrate`

2. **"Access denied" sur des données légitimes**
   - Vérifiez que l'utilisateur a bien une agency_id
   - Vérifiez que les données ont une agency_id

3. **Scope non appliqué**
   - Vérifiez que le trait est bien dans la classe
   - Vérifiez que vous n'êtes pas super-admin

### Désactiver temporairement

```php
// Pour une requête spécifique
$data = YourModel::withoutGlobalScopes()->get();

// Pour une relation
$user->units()->withoutGlobalScopes()->get();
```

## Performance

- Les indexes sur `agency_id` sont automatiquement utilisés
- Le scope est appliqué au niveau SQL, pas en PHP
- Compatible avec les relations Eloquent et les eager loading

## Sécurité renforcée

- Journalisation de toutes les tentatives d'accès inter-agences
- Exceptions claires en cas de violation
- Protection contre les injections SQL
- Validation stricte des permissions