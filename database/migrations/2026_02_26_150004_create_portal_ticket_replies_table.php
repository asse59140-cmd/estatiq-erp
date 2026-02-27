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
        Schema::create('portal_ticket_replies', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('portal_ticket_id');
            $table->unsignedBigInteger('agency_id');
            
            // Contenu
            $table->text('content');
            $table->boolean('is_internal')->default(false); // Réponse interne (non visible client)
            
            // Pièces jointes
            $table->json('attachments')->nullable();
            
            // Expéditeur (polymorphique)
            $table->string('sender_type'); // User, Employee, Tenant, Owner, etc.
            $table->unsignedBigInteger('sender_id');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('portal_ticket_id');
            $table->index('agency_id');
            $table->index('is_internal');
            $table->index(['sender_type', 'sender_id']);
            $table->index('created_at');
            
            // Contraintes de clé étrangère
            $table->foreign('portal_ticket_id')->references('id')->on('portal_tickets')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_ticket_replies');
    }
};