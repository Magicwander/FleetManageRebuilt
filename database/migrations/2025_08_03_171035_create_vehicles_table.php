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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('vin')->unique()->nullable();
            $table->string('color')->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid'])->default('gasoline');
            $table->decimal('capacity', 8, 2)->nullable(); // Fuel tank capacity or cargo capacity
            $table->integer('mileage')->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'retired'])->default('active');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['license_plate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
