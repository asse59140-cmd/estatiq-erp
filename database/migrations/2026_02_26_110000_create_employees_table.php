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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Informations personnelles
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Informations d'emploi
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('job_title')->nullable();
            
            // Rémunération
            $table->decimal('salary', 10, 2);
            $table->enum('salary_type', ['monthly', 'hourly', 'annual'])->default('monthly');
            $table->decimal('commission_rate', 5, 2)->default(0); // Pourcentage
            
            // Statut
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            
            // Adresse
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('France');
            
            // Informations administratives
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('rib')->nullable();
            $table->string('social_security_number')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->integer('number_of_children')->default(0);
            
            // Médias
            $table->json('profile_image')->nullable();
            $table->json('notes')->nullable();
            
            // Relations
            $table->unsignedBigInteger('user_id')->nullable(); // Lien vers le compte utilisateur
            $table->unsignedBigInteger('agency_id');
            $table->unsignedBigInteger('supervisor_id')->nullable(); // Manager direct
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('employee_number');
            $table->index('email');
            $table->index('agency_id');
            $table->index('user_id');
            $table->index('department');
            $table->index('status');
            $table->index('hire_date');
            $table->index('supervisor_id');
            
            // Contraintes de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};