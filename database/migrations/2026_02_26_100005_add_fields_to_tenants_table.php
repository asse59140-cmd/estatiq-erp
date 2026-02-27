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
        Schema::table('tenants', function (Blueprint $table) {
            // Ajout du lien vers Unit (nouvelle structure)
            $table->unsignedBigInteger('unit_id')->nullable()->after('property_id');
            
            // Informations supplémentaires
            $table->string('emergency_contact')->nullable()->after('phone');
            $table->string('emergency_phone')->nullable()->after('emergency_contact');
            $table->string('nationality')->nullable()->after('emergency_phone');
            $table->string('profession')->nullable()->after('nationality');
            $table->decimal('monthly_income', 10, 2)->nullable()->after('profession');
            
            // Lien vers le garant (sera créé dans la Phase 2)
            $table->unsignedBigInteger('guarantor_id')->nullable()->after('monthly_income');
            
            // Index pour les performances
            $table->index('unit_id');
            $table->index('guarantor_id');
            
            // Contrainte de clé étrangère
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn([
                'unit_id',
                'emergency_contact',
                'emergency_phone',
                'nationality',
                'profession',
                'monthly_income',
                'guarantor_id'
            ]);
        });
    }
};