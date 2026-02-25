<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Créer la table des Agences
        Schema::create('agencies', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->timestamps();
        });

        // 2. Créer la table de liaison entre Utilisateurs et Agences
        Schema::create('agency_user', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->timestamps();
        });

        // 3. Ajouter l'agence aux données existantes
        $tables = ['properties', 'owners', 'tenants', 'payments'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('agency_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down(): void { /* Pas nécessaire ici */ }
};