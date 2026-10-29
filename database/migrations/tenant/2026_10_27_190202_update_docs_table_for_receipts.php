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
        Schema::table('docs', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('order_id');
            $table->unsignedBigInteger('customer_id')->nullable()->after('warehouse_id');
            $table->string('notes')->nullable()->after('customer_id');
            $table->enum('type', ['receipt', 'issue', 'return', 'write_off'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
