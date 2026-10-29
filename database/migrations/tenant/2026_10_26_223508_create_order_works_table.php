<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_works', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('mechanic_id')->index(); // Кто делает

            $table->string('name'); // Замена помпы
            $table->integer('hours'); // Нормо-часы (целое число, например, 1 = 1 час)
            $table->integer('price'); // Стоимость работы в копейках

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_works');
    }
};
