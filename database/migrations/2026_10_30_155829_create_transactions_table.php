
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('vakalatnama_sticker_order_id')->nullable()->constrained('vakalatnama_sticker_orders')->restrictOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->index('transactions_invoice_item_id_index')->constrained('invoice_items')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            $table->string('trx_id', 150)->unique();
            $table->string('sender_mobile', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('pending')->index();
            $table->string('source', 20)->default('member')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['invoice_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index(['vakalatnama_sticker_order_id', 'status'],'transactions_sticker_order_status_index');
            $table->index(['verified_by', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
