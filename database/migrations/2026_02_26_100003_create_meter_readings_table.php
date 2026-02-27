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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('meter_id');
            $table->date('reading_date');
            $table->decimal('reading_value', 12, 3);
            $table->decimal('previous_reading', 12, 3)->nullable();
            $table->decimal('consumption', 12, 3)->nullable(); // Consommation depuis le dernier relevé
            
            // Type de relevé
            $table->enum('reading_type', ['actual', 'estimated', 'corrected'])->default('actual');
            
            // Qui a fait le relevé
            $table->string('read_by')->nullable(); // Nom de la personne ou "system"
            
            // Informations supplémentaires
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable(); // Photo du compteur
            $table->boolean('is_estimated')->default(false);
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('meter_id');
            $table->index('reading_date');
            $table->index(['meter_id', 'reading_date']);
            
            // Contrainte de clé étrangère
            $table->foreign('meter_id')->references('id')->on('meters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};