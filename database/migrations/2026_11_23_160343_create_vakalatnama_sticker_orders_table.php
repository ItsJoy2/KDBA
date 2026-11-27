
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
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number', 50)->unique();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 14, 2);
            $table->enum('payment_status', ['pending','processing','paid','rejected', 'cancelled',])->default('pending');
            $table->enum('status', ['pending','paid','cancelled',])->default('pending');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'payment_status'],'vs_orders_user_payment_idx');
            $table->index(['status', 'created_at'],'vs_orders_status_created_idx');
            $table->index( ['payment_status', 'created_at'], 'vs_orders_payment_created_idx' );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vakalatnama_sticker_orders');
    }
};
