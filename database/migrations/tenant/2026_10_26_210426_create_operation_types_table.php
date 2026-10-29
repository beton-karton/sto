<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Оприходование, Выдача в сервис, Списание брака
            $table->string('direction'); // income (приход) или outcome (расход)
            $table->integer('sign'); // 1 для прихода, -1 для расхода
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_types');
    }
};
