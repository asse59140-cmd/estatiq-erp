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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Numérotation
            $table->string('invoice_number')->unique();
            $table->string('reference')->nullable(); // Référence client
            
            // Client (polymorphique)
            $table->string('client_type'); // App\Models\Tenant, App\Models\Owner, etc.
            $table->unsignedBigInteger('client_id');
            
            $table->unsignedBigInteger('agency_id');
            
            // Dates
            $table->date('issue_date');
            $table->date('due_date');
            
            // Montants
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            
            // Devise et conditions
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_terms')->nullable(); // "30 jours", "À réception", etc.
            
            // Notes et conditions
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            // Pénalités de retard
            $table->decimal('late_fee_amount', 12, 2)->default(0);
            $table->decimal('late_fee_percentage', 5, 2)->default(0); // Pourcentage
            
            // Suivi des relances
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('overdue_notified_at')->nullable();
            
            // Statut
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('invoice_number');
            $table->index('agency_id');
            $table->index('client_type');
            $table->index('client_id');
            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
            $table->index(['client_type', 'client_id']);
            
            // Contraintes de clé étrangère
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};