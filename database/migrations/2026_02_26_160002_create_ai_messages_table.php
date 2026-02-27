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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('ai_conversation_id');
            
            // Contenu du message
            $table->string('role'); // user, assistant, system
            $table->text('content');
            $table->string('context')->nullable(); // Contexte spécifique (optionnel)
            
            // Métadonnées et métriques
            $table->json('metadata')->nullable();
            $table->integer('token_count')->nullable();
            $table->float('processing_time')->nullable(); // Temps en secondes
            $table->decimal('cost', 10, 6)->default(0); // Coût estimé
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('ai_conversation_id');
            $table->index('role');
            $table->index('created_at');
            $table->index(['ai_conversation_id', 'role']);
            $table->index(['ai_conversation_id', 'created_at']);
            
            // Contraintes de clé étrangère
            $table->foreign('ai_conversation_id')->references('id')->on('ai_conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};