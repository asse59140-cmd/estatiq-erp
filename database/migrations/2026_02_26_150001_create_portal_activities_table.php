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
        Schema::create('portal_activities', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('client_portal_id');
            $table->unsignedBigInteger('agency_id');
            
            // Activité
            $table->string('action'); // login, document_view, invoice_payment, etc.
            $table->json('data')->nullable(); // Données supplémentaires
            
            // Informations de connexion
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('client_portal_id');
            $table->index('agency_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['client_portal_id', 'created_at']);
            
            // Contraintes de clé étrangère
            $table->foreign('client_portal_id')->references('id')->on('client_portals')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_activities');
    }
};