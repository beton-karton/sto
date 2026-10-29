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
            $table->unsignedBigInteger('customer_id')->index(); // Ссылка на локальную таблицу customers
            $table->string('model_id')->nullable()->index();    // Ссылка на глобальную таблицу models (AUDI_A4)
            $table->integer('year')->nullable();
            $table->string('vin', 17)->nullable()->index();
            $table->string('license_plate')->nullable()->index();
            $table->text('description')->nullable();            // "3.2 MT 4WD, цвет синий"
            $table->timestamps();
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
