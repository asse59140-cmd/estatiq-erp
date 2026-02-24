<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('lease_document')->nullable(); // Pour le contrat de bail
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->json('images')->nullable(); // Pour stocker plusieurs photos
        });
    }

    public function down(): void {
        Schema::table('tenants', function (Blueprint $table) { $table->dropColumn('lease_document'); });
        Schema::table('properties', function (Blueprint $table) { $table->dropColumn('images'); });
    }
};