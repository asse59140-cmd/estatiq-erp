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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('address');
            $table->decimal('price', 15, 2);
            $table->string('type'); // Changé en string pour plus de souplesse
            $table->string('status')->default('Disponible'); // Changé en string
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // Ajouté au cas où tu veux mettre des photos
            $table->foreignId('owner_id')->nullable()->constrained()->onDelete('cascade'); // Ajouté pour lier aux propriétaires
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};