
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->string('fee_key', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('amount', 12, 2);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['invoice_id', 'fee_key'],'invoice_items_invoice_fee_unique');
            $table->index(['fee_key', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('invoice_items');
    }
};
