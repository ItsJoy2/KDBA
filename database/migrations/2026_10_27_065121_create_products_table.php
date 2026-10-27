<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();

            $table->decimal('buy_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);

            $table->boolean('has_serial')->default(false);

            $table->unsignedBigInteger('serial_start')->nullable();
            $table->unsignedBigInteger('serial_end')->nullable();

            // Next serial number available for allocation.
            $table->unsignedBigInteger('current_serial')->nullable();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();

            $table->index(['status', 'name']);
            $table->index('has_serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};