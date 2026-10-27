
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Basic product information
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();

            // Product pricing
            $table->decimal('buy_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);

            // Stock management
            $table->unsignedInteger('stock_quantity')->default(0);

            // Serial number tracking
            $table->boolean('has_serial')->default(false);
            $table->boolean('track_serials')->default(false);

            // Serial number range
            $table->unsignedBigInteger('serial_start')->nullable();
            $table->unsignedBigInteger('serial_end')->nullable();

            // Next serial number available for allocation
            $table->unsignedBigInteger('current_serial')->nullable();

            // Product status
            $table->enum('status', ['active', 'inactive'])->default('active');

            // User who created the product
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // User who last updated the product
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Created and updated timestamps
            $table->timestamps();

            // Database indexes
            $table->index(['status', 'name']);
            $table->index(['status', 'created_at']);
            $table->index('has_serial');
            $table->index('track_serials');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
