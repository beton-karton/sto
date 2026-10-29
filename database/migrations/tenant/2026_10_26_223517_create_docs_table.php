<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index()->nullable(); // К какому ЗН относится
            $table->unsignedBigInteger('user_id')->index(); // Кладовщик

            $table->string('type'); // issue (выдача), return (возврат)

            $table->boolean('is_confirmed')->default(false)->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('confirmed_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docs');
    }
};
