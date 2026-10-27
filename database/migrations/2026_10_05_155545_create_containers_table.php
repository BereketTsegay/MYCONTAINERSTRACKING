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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('container_number')->unique()->nullable();
            $table->string('container_name')->nullable();
            $table->enum('container_size', ['20ft', '40ft', '40ft HC', '45ft'])->default('40ft HC');
            $table->enum('container_type', ['SOC', 'COC'])->default('SOC');
            $table->string('booking_number')->nullable();
            $table->string('seal_number')->nullable();

            $table->foreignId('voyage_id')->constrained('voyages')->onDelete('cascade');
            $table->foreignId('transitor_id')->constrained('parties')->onDelete('cascade');
            $table->foreignId('shipper_id')->constrained('parties')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');

            $table->integer('vgm')->nullable();
            $table->decimal('tare_weight', 10, 2)->nullable()->default(3900);
            $table->decimal('max_gross_weight', 10, 2)->nullable()->default(32500);
            $table->integer('total_pkg')->default(950)->nullable();

            $table->enum('status', ['empty', 'loaded', 'in_transit', 'discharged'])->default('empty');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
