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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Informations du fichier
            $table->string('name');
            $table->string('file_path');
            $table->integer('file_size'); // En octets
            $table->string('mime_type');
            
            // Classification
            $table->string('document_type'); // Contrat, Facture, ID, etc.
            $table->string('category')->nullable(); // Catégorie personnalisée
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            
            // Gestion des versions
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            
            // Dates importantes
            $table->date('expires_at')->nullable();
            
            // Relations
            $table->unsignedBigInteger('uploaded_by'); // User ID
            $table->unsignedBigInteger('agency_id');
            
            // Polymorphic relation (peut appartenir à n'importe quel modèle)
            $table->unsignedBigInteger('documentable_id')->nullable();
            $table->string('documentable_type')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('agency_id');
            $table->index('uploaded_by');
            $table->index('document_type');
            $table->index('category');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index(['documentable_id', 'documentable_type']);
            
            // Contraintes de clé étrangère
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};