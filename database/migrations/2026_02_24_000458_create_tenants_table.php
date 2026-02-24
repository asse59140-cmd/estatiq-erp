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
            
            // Les colonnes exactes que ton formulaire attend
            $table->string('full_name'); 
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // La liaison avec la propriété (villa/appartement)
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Les dates et documents du bail
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->string('lease_document')->nullable();
            
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