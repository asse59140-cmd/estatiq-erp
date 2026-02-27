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
        Schema::create('portal_tickets', function (Blueprint $table) {
            $table->id();
            
            // Numéro de ticket
            $table->string('ticket_number')->unique();
            
            $table->unsignedBigInteger('client_portal_id');
            $table->unsignedBigInteger('agency_id');
            
            // Détails du ticket
            $table->string('subject');
            $table->text('description');
            $table->enum('category', ['maintenance', 'billing', 'technical', 'general', 'complaint', 'suggestion'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            
            // Assignation
            $table->unsignedBigInteger('assigned_to')->nullable();
            
            // Dates importantes
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Résolution
            $table->text('resolution_notes')->nullable();
            
            // Pièces jointes
            $table->json('attachments')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('ticket_number');
            $table->index('client_portal_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('priority');
            $table->index('category');
            $table->index('assigned_to');
            $table->index('created_at');
            $table->index(['client_portal_id', 'status']);
            
            // Contraintes de clé étrangère
            $table->foreign('client_portal_id')->references('id')->on('client_portals')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_tickets');
    }
};