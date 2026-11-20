
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->enum('type', ['income', 'expense']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
             $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
