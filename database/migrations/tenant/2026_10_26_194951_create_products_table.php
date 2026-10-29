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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('oem_number')->index();
            $table->string('brand');
            $table->string('article')->nullable();
            $table->string('name');
            $table->integer('sale_price')->default(0);     // Цена продажи
            $table->integer('average_price')->default(0);  // Средневзвешенная себестоимость
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
