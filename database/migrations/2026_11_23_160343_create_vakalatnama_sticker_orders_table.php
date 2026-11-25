<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vakalatnama_sticker_orders', function (Blueprint $table) {
            $table->id();

            // Member who owns the stickers
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Admin or member who created the order
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Unique order reference
            $table->string('order_number', 40)->unique();

            $table->unsignedSmallInteger('quantity');

            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);

            // Existing invoice integration
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->nullOnDelete();

            // pending, approved, rejected
            $table->string('status', 20)->default('pending');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_sticker_orders');
    }
};
