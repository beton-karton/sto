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
        Schema::create('brands', function (Blueprint $table) {
            $table->string('id')->primary(); // Например, 'AUDI'
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('models', function (Blueprint $table) {
            $table->string('id')->primary(); // Например, 'AUDI_A4'
            $table->string('brand_id');
            $table->string('name');
            $table->integer('year_from')->nullable();
            $table->integer('year_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands_and_models_tables');
    }
};
