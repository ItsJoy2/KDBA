
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Unique order number
            $table->string('order_number', 40)->unique();

            // Order owner
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // User who purchased on behalf of the owner
            $table->foreignId('purchased_by')->nullable()->constrained('users')->nullOnDelete();

            // Order total amount
            $table->decimal('total_amount', 12, 2)->default(0);

            // Payment status
            $table->enum('payment_status', ['pending','paid', 'failed',])->default('pending');

            // Order status
            $table->enum('order_status', ['pending','processing','approved','completed','cancelled',])->default('pending');

            // Created and updated timestamps
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['purchased_by', 'created_at']);
            $table->index(['order_status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
