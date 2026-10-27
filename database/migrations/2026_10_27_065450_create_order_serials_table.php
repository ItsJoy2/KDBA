<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->unsignedBigInteger('serial_number');

            $table->timestamps();

            // A product serial can only be allocated once.
            $table->unique(
                ['product_id', 'serial_number'],
                'order_serials_product_serial_unique'
            );

            $table->index(['order_id', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_serials');
    }
};