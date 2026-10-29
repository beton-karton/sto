<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'license_plate']);
            $table->string('color')->nullable()->after('year'); // Добавляем цвет
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('license_plate')->nullable()->after('mileage'); // Переносим номер в ЗН
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('license_plate')->nullable();
            $table->dropColumn('color');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('license_plate');
        });
    }
};
