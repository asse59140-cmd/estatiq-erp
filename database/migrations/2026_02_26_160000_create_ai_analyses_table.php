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
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('agency_id');
            
            // Type et contenu de l'analyse
            $table->string('analysis_type'); // market_trends, tenant_behavior, etc.
            $table->json('input_data'); // Données d'entrée
            $table->json('output_data'); // Résultats de l'analyse
            
            // Performance et fiabilité
            $table->float('confidence_score')->default(0.0); // Score de confiance (0-1)
            $table->string('provider'); // gemini, openai, anthropic
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'validated', 'rejected'])->default('pending');
            
            // Erreurs et métriques
            $table->text('error_message')->nullable();
            $table->float('processing_time')->nullable(); // Temps en secondes
            $table->decimal('cost', 10, 6)->default(0); // Coût de l'API
            
            // Métadonnées supplémentaires
            $table->json('metadata')->nullable();
            
            // Validation humaine
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('feedback')->nullable(); // Feedback de l'utilisateur
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('agency_id');
            $table->index('analysis_type');
            $table->index('provider');
            $table->index('status');
            $table->index('confidence_score');
            $table->index('created_at');
            $table->index(['agency_id', 'analysis_type']);
            $table->index(['agency_id', 'status']);
            $table->index(['analysis_type', 'created_at']);
            
            // Contraintes de clé étrangère
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};