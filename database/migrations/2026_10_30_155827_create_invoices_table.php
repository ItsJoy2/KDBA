
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->string('invoice_type', 30);
            $table->string('cycle_key', 30);
            $table->string('member_status', 30);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('pending')->index();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'invoice_type', 'cycle_key'],'invoices_user_type_cycle_unique');
            $table->index(['user_id', 'status']);
            $table->index(['invoice_type', 'cycle_key']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
