<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('operation_type_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('doc_id')->index();

            $table->integer('amount'); // Количество

            // ДВЕ ЦЕНЫ:
            $table->integer('purchase_price')->default(0); // Себестоимость (закупка)
            $table->integer('sale_price')->default(0);     // Цена продажи (для клиента)

            $table->integer('sign'); // 1 или -1
            $table->boolean('is_confirmed')->default(false)->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('confirmed_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
