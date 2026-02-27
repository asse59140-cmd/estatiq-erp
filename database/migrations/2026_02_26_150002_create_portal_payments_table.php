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
        Schema::create('portal_payments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('client_portal_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('agency_id');
            
            // Montants
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EUR');
            
            // Méthode et fournisseur
            $table->string('payment_method'); // credit_card, bank_transfer, paypal, etc.
            $table->string('payment_provider'); // stripe, paypal, razorpay, etc.
            $table->string('transaction_id')->nullable();
            
            // Statut
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            
            // Données de paiement
            $table->json('payment_data')->nullable();
            
            // Remboursement
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            // Informations de connexion
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('client_portal_id');
            $table->index('invoice_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('payment_provider');
            $table->index('transaction_id');
            $table->index('paid_at');
            $table->index(['client_portal_id', 'status']);
            
            // Contraintes de clé étrangère
            $table->foreign('client_portal_id')->references('id')->on('client_portals')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_payments');
    }
};