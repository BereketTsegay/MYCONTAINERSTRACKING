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
        Schema::create('voyages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->onDelete('cascade');
            $table->string('voyage_number')->nullable();
            $table->foreignId('party_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('origin_port')->nullable();
            $table->string('destination_port')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voyages');
    }
};
