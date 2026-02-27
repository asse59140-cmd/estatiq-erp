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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('agency_id');
            
            // Période d'évaluation
            $table->date('review_period_start');
            $table->date('review_period_end');
            
            // Évaluation
            $table->integer('overall_rating')->nullable(); // 1-5
            $table->json('goals_achievement')->nullable();
            $table->json('skills_assessment')->nullable();
            $table->json('areas_for_improvement')->nullable();
            $table->json('strengths')->nullable();
            $table->json('development_plan')->nullable();
            $table->json('recommendations')->nullable();
            
            // Recommandations
            $table->decimal('salary_recommendation', 10, 2)->nullable();
            $table->boolean('promotion_recommendation')->default(false);
            
            // Statut et commentaires
            $table->enum('status', ['draft', 'pending', 'completed', 'approved'])->default('draft');
            $table->text('employee_comments')->nullable();
            $table->text('reviewer_comments')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('employee_id');
            $table->index('reviewer_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('review_period_start');
            $table->index('review_period_end');
            
            // Contraintes de clé étrangère
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};