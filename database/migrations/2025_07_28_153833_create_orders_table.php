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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->uuid('product_id')->nullable();
            $table->uuid('warehouse_product_id')->nullable();

            $table->integer('quantity')->default(1);
            $table->text('text')->nullable();

            $table->enum('status', ['new', 'in_progress', 'done'])->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
