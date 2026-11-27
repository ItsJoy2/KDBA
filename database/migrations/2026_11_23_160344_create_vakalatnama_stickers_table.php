
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vakalatnama_stickers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('vakalatnama_sticker_orders')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('record_number', 50)->unique();
            $table->string('unique_number', 20)->nullable()->unique();
            $table->enum('status', ['pending','issued','revoked',])->default('pending');
            $table->unsignedInteger('print_count')->default(0);
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_printed_at')->nullable();
            $table->foreignId('last_printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['order_id', 'status'], 'vs_stickers_order_status_idx' );
            $table->index( ['user_id', 'status'],  'vs_stickers_user_status_idx');
            $table->index(['status', 'printed_at'], 'vs_stickers_status_printed_idx');
        });

        DB::statement("
            ALTER TABLE vakalatnama_stickers
            ADD CONSTRAINT chk_vs_unique_number_format
            CHECK (
                unique_number IS NULL
                OR unique_number REGEXP '^[0-9]{18}$'
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_stickers');
    }
};
