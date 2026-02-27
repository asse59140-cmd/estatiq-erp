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
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            
            // Document et entité associée
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('requestable_id')->nullable();
            $table->string('requestable_type')->nullable();
            $table->unsignedBigInteger('agency_id');
            
            // Informations de signature
            $table->string('envelope_id')->nullable(); // ID chez le fournisseur
            $table->string('provider'); // docusign, dropbox_sign, etc.
            $table->enum('status', ['pending', 'sent', 'viewed', 'completed', 'expired', 'cancelled', 'declined'])->default('pending');
            $table->string('request_type'); // lease_contract, invoice, maintenance_contract, etc.
            $table->string('title');
            $table->text('description')->nullable();
            
            // Signataires
            $table->json('signers'); // Tableau des signataires avec leurs infos
            
            // Document signé
            $table->string('signed_document_path')->nullable();
            
            // Dates importantes
            $table->timestamp('request_date')->nullable();
            $table->timestamp('sent_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->timestamp('expired_date')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            
            // Webhook et données supplémentaires
            $table->json('webhook_data')->nullable();
            $table->json('metadata')->nullable();
            
            // Utilisateur ayant créé la demande
            $table->unsignedBigInteger('created_by');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('document_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('provider');
            $table->index('request_type');
            $table->index('envelope_id');
            $table->index(['requestable_id', 'requestable_type']);
            $table->index('created_by');
            $table->index('completed_date');
            $table->index('expired_date');
            
            // Contraintes de clé étrangère
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
    }
};