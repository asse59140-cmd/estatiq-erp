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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('agency_id');
            
            // Type et dates de congé
            $table->enum('leave_type', ['annual', 'sick', 'personal', 'maternity', 'paternity', 'unpaid', 'emergency', 'bereavement']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            
            // Détails
            $table->text('reason')->nullable();
            
            // Statut et approbation
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Remplacement
            $table->unsignedBigInteger('replacement_employee_id')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('employee_id');
            $table->index('agency_id');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('approved_by');
            $table->index('replacement_employee_id');
            
            // Contraintes de clé étrangère
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('replacement_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};