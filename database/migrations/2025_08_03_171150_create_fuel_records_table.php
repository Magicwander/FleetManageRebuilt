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
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->date('fuel_date');
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric'])->default('gasoline');
            $table->decimal('quantity', 8, 2); // Liters or kWh
            $table->decimal('cost_per_liter', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->integer('mileage');
            $table->string('location')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'fuel_date']);
            $table->index(['driver_id', 'fuel_date']);
            $table->index(['fuel_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};
