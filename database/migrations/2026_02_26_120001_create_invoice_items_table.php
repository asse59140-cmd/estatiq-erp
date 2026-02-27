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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('agency_id');
            
            // Description et quantité
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            
            // Taxes et remises
            $table->decimal('tax_rate', 5, 2)->default(20); // TVA 20%
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            
            // Référence (polymorphique)
            $table->string('item_type')->nullable(); // Type d'élément source
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de l'élément source
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index('invoice_id');
            $table->index('agency_id');
            $table->index('item_type');
            $table->index('reference_id');
            $table->index(['item_type', 'reference_id']);
            
            // Contraintes de clé étrangère
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};