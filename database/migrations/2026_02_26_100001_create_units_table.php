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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            
            // Identifiant de l'unité (ex: "A101", "B-205")
            $table->string('unit_number')->nullable();
            
            // Relations
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('agency_id');
            
            // Caractéristiques physiques
            $table->integer('floor')->default(1);
            $table->enum('unit_type', ['studio', 'apartment', 'duplex', 'penthouse', 'office', 'retail', 'warehouse'])
                  ->default('apartment');
            
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('area_sqm', 8, 2)->nullable(); // Surface en m²
            
            // Tarification
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->nullable(); // Dépôt de garantie
            
            // Équipements et commodités
            $table->boolean('furnished')->default(false);
            $table->boolean('balcony')->default(false);
            $table->boolean('parking_space')->default(false);
            
            // Statut de l'unité
            $table->enum('status', ['available', 'occupied', 'maintenance', 'unavailable'])
                  ->default('available');
            
            // Descriptions et médias
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // Équipements spécifiques à l'unité
            $table->json('images')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('building_id');
            $table->index('agency_id');
            $table->index('unit_number');
            $table->index('status');
            $table->index('unit_type');
            
            // Contraintes de clé étrangère
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};