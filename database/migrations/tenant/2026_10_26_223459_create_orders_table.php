<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('master_id')->index(); // Мастер-приёмщик

            $table->string('status'); // open, in_progress, ready_for_payment, completed
            $table->text('reason');   // Причина обращения
            $table->integer('mileage'); // Пробег

            // Якоря для оценки эффективности
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('exited_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
