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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('agency_id');
            
            // Détails du paiement
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'credit_card', 'debit_card', 'online', 'other']);
            $table->date('payment_date');
            
            // Références
            $table->string('reference')->nullable(); // Référence de transaction
            $table->string('transaction_id')->nullable(); // ID de transaction bancaire
            
            // Notes
            $table->text('notes')->nullable();
            
            // Traité par
            $table->unsignedBigInteger('processed_by')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('invoice_id');
            $table->index('agency_id');
            $table->index('payment_method');
            $table->index('payment_date');
            $table->index('processed_by');
            
            // Contraintes de clé étrangère
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};