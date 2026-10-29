
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('email', 254)->nullable()->unique();
            $table->string('mobile', 20)->nullable()->unique();
            $table->string('password');
            $table->rememberToken();
            $table->string('father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('nid_passport_number', 100)->nullable()->unique();
            $table->json('nid_images')->nullable();
            $table->string('photo')->nullable();
            $table->string('membership_number', 50)->nullable()->unique();
            $table->date('bar_join_date')->nullable();
            $table->string('sanad_number', 100)->nullable();
            $table->date('sanad_date')->nullable();
            $table->string('status', 30)->default('Active')->index();
            $table->string('approval_status', 20)->default('pending');
            $table->string('member_status', 30)->default('active');
            $table->foreignId('lawyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('two_factor_phone', 20)->nullable();
            $table->timestamp('two_factor_phone_verified_at')->nullable();
            $table->text('two_factor_authenticator_secret')->nullable();
            $table->timestamp('two_factor_authenticator_confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at']);
            $table->index(['approval_status', 'member_status'], 'users_approval_member_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
