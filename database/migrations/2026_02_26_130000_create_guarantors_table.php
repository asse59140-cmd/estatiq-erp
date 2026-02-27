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
        Schema::create('guarantors', function (Blueprint $table) {
            $table->id();
            
            // Informations personnelles
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('profession')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            
            // Adresse
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('France');
            
            // Pièce d'identité
            $table->string('id_number')->nullable();
            $table->enum('id_type', ['passport', 'national_id', 'driver_license', 'other'])->nullable();
            
            // Garantie
            $table->string('relationship_to_tenant')->nullable(); // Relation avec le locataire
            $table->decimal('guarantee_amount', 12, 2)->nullable(); // Montant de la garantie
            $table->enum('guarantee_type', ['full', 'partial', 'limited'])->default('full');
            
            // Statut
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            
            // Vérification
            $table->boolean('documents_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            // Relations
            $table->unsignedBigInteger('agency_id');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('agency_id');
            $table->index('email');
            $table->index('status');
            $table->index('documents_verified');
            $table->index('verified_by');
            
            // Contraintes de clé étrangère
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantors');
    }
};