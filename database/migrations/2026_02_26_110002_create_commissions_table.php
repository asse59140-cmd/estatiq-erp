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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('agency_id');
            
            // Montant et calcul
            $table->decimal('amount', 10, 2);
            $table->decimal('rate_applied', 6, 4); // Taux appliqué (ex: 0.05 pour 5%)
            $table->decimal('base_amount', 10, 2)->nullable(); // Montant de base pour le calcul
            
            // Type et description
            $table->string('commission_type'); // location, vente, service, etc.
            $table->text('description')->nullable();
            
            // Référence à l'opération source (polymorphique)
            $table->string('reference_type')->nullable(); // Type d'entité source
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de l'entité source
            
            // Statut et paiement
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('employee_id');
            $table->index('agency_id');
            $table->index('commission_type');
            $table->index('status');
            $table->index('payment_date');
            $table->index(['reference_type', 'reference_id']);
            
            // Contraintes de clé étrangère
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};