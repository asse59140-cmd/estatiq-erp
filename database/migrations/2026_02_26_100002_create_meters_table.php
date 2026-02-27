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
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            
            // Identifiant unique du compteur
            $table->string('meter_number')->unique();
            
            // Relations
            $table->unsignedBigInteger('unit_id')->nullable(); // Peut être lié à une unité ou directement au bâtiment
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('agency_id');
            
            // Type de compteur et utilité
            $table->enum('meter_type', ['individual', 'collective', 'submeter'])->default('individual');
            $table->enum('utility_type', ['electricity', 'water', 'gas', 'heating', 'cooling', 'other']);
            
            // Installation et relevés
            $table->date('installation_date');
            $table->decimal('initial_reading', 12, 3)->default(0);
            $table->decimal('current_reading', 12, 3)->default(0);
            
            // Unités et facturation
            $table->string('unit_of_measure')->default('kWh'); // kWh, m³, etc.
            $table->decimal('multiplier', 8, 2)->default(1); // Multiplicateur pour les sous-compteurs
            
            // Statut et fournisseur
            $table->enum('status', ['active', 'inactive', 'defective', 'removed'])->default('active');
            $table->string('supplier_name')->nullable();
            $table->string('contract_number')->nullable();
            
            // Fréquence de relevé
            $table->enum('billing_frequency', ['monthly', 'bimonthly', 'quarterly', 'semiannual', 'annual'])
                  ->default('monthly');
            
            // Informations supplémentaires
            $table->text('location_description')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('meter_number');
            $table->index('unit_id');
            $table->index('building_id');
            $table->index('agency_id');
            $table->index('utility_type');
            $table->index('status');
            
            // Contraintes de clé étrangère
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};