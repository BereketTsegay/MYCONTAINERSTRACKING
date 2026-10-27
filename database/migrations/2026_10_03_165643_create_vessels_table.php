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
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('imo_number')->unique();
            $table->foreignId('party_id')->constrained()->onDelete('cascade');
            $table->string('mmsi_number')->unique()->nullable();
            $table->string('call_sign')->unique()->nullable();
            $table->string('flag')->nullable();
            $table->string('vessel_type')->nullable();
            $table->string('gross_tonnage')->nullable();
            $table->string('net_tonnage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};
