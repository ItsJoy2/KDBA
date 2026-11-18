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

            // Basic Information
            $table->string('full_name', 150);
            $table->string('email', 254)->nullable()->unique();
            $table->string('mobile', 20)->nullable()->unique();
            $table->string('password');
            $table->rememberToken();

            // Personal Information
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

            // Membership Information
            $table->string('membership_number', 50)->nullable()->unique();
            $table->date('bar_join_date')->nullable();
            $table->string('sanad_number', 100)->nullable();
            $table->date('sanad_date')->nullable();

            $table->string('status', 30)->default('Active')->index();
            $table->string('approval_status', 20)->default('pending');
            $table->string('member_status', 30)->default('active');

            // Member Classification
            $table->string('member_type', 30)->nullable();

            // Chamber Allocation
            $table->string('chamber_room_no', 50)->nullable();
            $table->string('chamber_type', 20)->nullable();

            // Lawyer Relationship
            $table->foreignId('lawyer_id')->nullable()->constrained('users')->nullOnDelete();

            // Two Factor Authentication
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_phone', 20)->nullable();
            $table->timestamp('two_factor_phone_verified_at')->nullable();
            $table->text('two_factor_authenticator_secret')->nullable();
            $table->timestamp('two_factor_authenticator_confirmed_at')->nullable();

            // Login Information
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            // Created By / Updated By
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps & Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['approval_status', 'member_status'],'users_approval_member_status_index');
            $table->index('member_type','users_member_type_index');
            $table->index(['chamber_room_no', 'chamber_type'],'users_chamber_allocation_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
