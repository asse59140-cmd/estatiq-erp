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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('agency_id');
            $table->unsignedBigInteger('user_id');
            
            // Informations de conversation
            $table->string('title')->default('Nouvelle conversation');
            $table->string('context')->nullable(); // Type de contexte (support, analyse, etc.)
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->string('provider'); // gemini, openai, anthropic
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            // Statistiques
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('message_count')->default(0);
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('agency_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('provider');
            $table->index('last_activity_at');
            $table->index(['agency_id', 'user_id']);
            $table->index(['agency_id', 'status']);
            
            // Contraintes de clé étrangère
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};