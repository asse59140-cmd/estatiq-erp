<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de l'immeuble
            $table->string('address'); // Adresse complète
            $table->string('city');
            $table->string('postal_code', 10);
            $table->string('country')->default('France');
            
            $table->enum('building_type', ['residential', 'commercial', 'mixed', 'office', 'retail'])
                  ->default('residential');
            
            $table->year('construction_year')->nullable();
            $table->integer('total_floors')->default(1);
            $table->text('description')->nullable();
            
            $table->json('amenities')->nullable(); // Équipements (ascenseur, parking, etc.)
            $table->integer('parking_spaces')->default(0);
            $table->integer('elevator_count')->default(0);
            
            $table->enum('energy_rating', ['A', 'B', 'C', 'D', 'E', 'F', 'G'])->nullable();
            
            // Coordonnées GPS pour la cartographie
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            
            // Images multiples
            $table->json('images')->nullable();
            
            // Relations multi-tenant
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('agency_id');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('agency_id');
            $table->index('owner_id');
            $table->index(['city', 'postal_code']);
            $table->index('building_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};