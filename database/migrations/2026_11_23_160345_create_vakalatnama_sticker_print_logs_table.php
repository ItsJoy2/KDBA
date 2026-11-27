
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
            $table->foreignId('sticker_id')->constrained('vakalatnama_stickers')->restrictOnDelete();
            $table->foreignId('printed_by')->constrained('users')->restrictOnDelete();
            $table->enum('print_type', ['first_print','reprint',]);
            $table->timestamp('printed_at');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index( ['sticker_id', 'printed_at'],'vs_print_logs_sticker_date_idx');
            $table->index( ['printed_by', 'printed_at'], 'vs_print_logs_user_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_sticker_print_logs');
    }
};
