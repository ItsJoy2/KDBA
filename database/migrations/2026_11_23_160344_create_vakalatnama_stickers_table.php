<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vakalatnama_stickers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('vakalatnama_sticker_orders')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Never expose this number in normal listings or APIs
            $table->string('unique_number', 60)->unique();

            // pending, ready, printed
            $table->string('status', 20)->default('pending');

            $table->timestamp('printed_at')->nullable();

            $table->unsignedSmallInteger('print_count')->default(0);

            $table->foreignId('last_printed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_stickers');
    }
};
