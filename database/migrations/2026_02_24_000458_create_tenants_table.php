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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // Prénom
            $table->string('last_name');  // Nom
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('id_card_number')->nullable(); // N° de pièce d'identité
            $table->string('status')->default('Actif');   // Actif, Partant, Ancien
            $table->date('birth_date')->nullable();       // Date de naissance
            $table->text('notes')->nullable();            // Commentaires éventuels
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};