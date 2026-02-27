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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('agency_id');
            
            $table->date('date');
            $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();
            
            // Statut de présence
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('present');
            
            // Heures de travail
            $table->decimal('work_hours', 5, 2)->default(0); // Heures travaillées
            $table->decimal('overtime_hours', 5, 2)->default(0); // Heures supplémentaires
            
            // Retards et départs anticipés
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            
            // Localisation pour le contrôle de présence
            $table->json('location_check_in')->nullable();
            $table->json('location_check_out')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('employee_id');
            $table->index('agency_id');
            $table->index('date');
            $table->index(['employee_id', 'date']);
            $table->index('status');
            
            // Contraintes de clé étrangère
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};