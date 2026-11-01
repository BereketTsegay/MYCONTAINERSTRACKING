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
        // 1. CATEGORIES TABLE
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['pklist', 'house_bill', 'edpass']); 
            $table->timestamps();
        });

        // 2. UNITS OF MEASUREMENT (UOM) TABLE
        Schema::create('units_of_measurement', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Box, Bottle, Kilogram
            $table->string('code')->unique(); // e.g., BX, BT, KG
            $table->timestamps();
        });

        // 3. PRODUCTS TABLE
        Schema::create('products', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('pk_category_id')->nullable();
            $table->unsignedBigInteger('hbill_category_id')->nullable();
            $table->unsignedBigInteger('edpass_category_id')->nullable();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            
            // Global HS Code (If it applies to all variations of this product)
            $table->string('hs_code', 12)->nullable(); 
            
            $table->boolean('has_variations')->default(false);
            $table->timestamps();

            $table->foreign('pk_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('hbill_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('edpass_category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // 4. PRODUCT VARIATIONS TABLE
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_of_measurement_id')->constrained('units_of_measurement')->onDelete('cascade');
            $table->decimal('qty_per_unit', 10, 2); 
            $table->string('variation_sku')->unique()->nullable();
            $table->decimal('price', 15, 2)->nullable();

            // Shipping & Customs Fields
            $table->decimal('weight', 8, 3)->nullable(); // e.g., 12.345 kg
            $table->decimal('length', 8, 2)->nullable(); // e.g., 30.50 cm
            $table->decimal('width', 8, 2)->nullable();  // e.g., 20.00 cm
            $table->decimal('height', 8, 2)->nullable(); // e.g., 15.00 cm
            
            // Variant-specific HS Code (Overrides product HS code if a larger package uses a different tariff code)
            $table->string('variation_hs_code', 12)->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units_of_measurement');
        Schema::dropIfExists('categories');
    }
};
