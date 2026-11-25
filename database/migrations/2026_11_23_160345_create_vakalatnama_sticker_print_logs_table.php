<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vakalatnama_sticker_print_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sticker_id')
                ->constrained('vakalatnama_stickers')
                ->cascadeOnDelete();

            $table->foreignId('printed_by')
                ->constrained('users')
                ->restrictOnDelete();

            // original, reprint
            $table->string('print_type', 20);

            $table->timestamp('printed_at');

            $table->timestamps();

            $table->index(['sticker_id', 'print_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_sticker_print_logs');
    }
};
