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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            
            // Numérotation
            $table->string('credit_note_number')->unique();
            
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('agency_id');
            
            // Montants
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            
            // Détails
            $table->text('reason');
            $table->text('notes')->nullable();
            
            // Statut et approbation
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Dates
            $table->date('issue_date');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('credit_note_number');
            $table->index('invoice_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('approved_by');
            $table->index('issue_date');
            
            // Contraintes de clé étrangère
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};