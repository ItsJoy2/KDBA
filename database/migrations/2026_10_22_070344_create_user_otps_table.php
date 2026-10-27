<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Examples: login_2fa, password_reset, email_verification
            $table->string('purpose', 50);

            $table->enum('channel', ['email', 'sms']);

            // Store a cryptographic hash, never the plaintext OTP.
            $table->string('code_hash', 255);

            // Destination used when the code was issued.
            $table->string('sent_to', 254);

            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);

            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('locked_until')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'purpose', 'created_at'],
                'user_otps_user_purpose_created_idx'
            );

            $table->index(
                ['expires_at', 'consumed_at'],
                'user_otps_expiry_consumed_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};