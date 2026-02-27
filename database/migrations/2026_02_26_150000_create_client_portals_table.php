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
        Schema::create('client_portals', function (Blueprint $table) {
            $table->id();
            
            // Utilisateur et client
            $table->unsignedBigInteger('user_id');
            $table->string('client_type'); // App\Models\Tenant, App\Models\Owner, etc.
            $table->unsignedBigInteger('client_id');
            
            // Configuration du portail
            $table->enum('portal_type', ['tenant', 'owner', 'guarantor', 'both'])->default('tenant');
            $table->string('access_code')->unique();
            $table->boolean('is_active')->default(true);
            
            // Statistiques de connexion
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            
            // Préférences
            $table->json('preferences')->nullable();
            
            // API Token pour accès mobile/automatisé
            $table->string('api_token')->unique()->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            // Relations
            $table->unsignedBigInteger('agency_id');
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('user_id');
            $table->index('agency_id');
            $table->index('client_type');
            $table->index('client_id');
            $table->index('portal_type');
            $table->index('is_active');
            $table->index('access_code');
            $table->index('api_token');
            $table->index('token_expires_at');
            $table->index(['client_type', 'client_id']);
            
            // Contraintes de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            
            // Contraintes d'unicité
            $table->unique(['user_id', 'client_type', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_portals');
    }
};