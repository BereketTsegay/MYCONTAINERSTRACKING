<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MASTER LOADING LIST HEADER
        Schema::create('loading_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('containers')->onDelete('cascade');
            $table->foreignId('voyage_id')->constrained('voyages')->onDelete('cascade');
            $table->date('loading_date');
            $table->string('loader_name')->nullable();
            $table->string('document_status')->default('draft'); // draft, locked, invoiced
            $table->timestamps();
        });

        // 2. ITEMISED LOADING LIST DETAILS (WITH SECTOR MAP ALLOCATIONS)
        Schema::create('loading_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loading_list_id')->constrained('loading_lists')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('declared_item_name'); // e.g., GLYCERINE OIL 132G
            $table->string('uom_code'); // e.g., CTN, BAG, PCS
            $table->integer('total_qty'); // Total items assigned
            
            // Structured Sector Distribution Maps (Tracking Columns A1-A12, H1-H12)
            $table->json('sector_allocations')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_list_items');
        Schema::dropIfExists('loading_lists');
    }
};
