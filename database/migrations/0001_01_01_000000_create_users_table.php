<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lfp_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('lfp_firms')->cascadeOnDelete();
            $table->string('username');
            $table->integer('role');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['firm_id', 'username']);

            // Target for the composite foreign keys on child tables below. It is
            // what makes a child row's firm_id physically unable to disagree
            // with its parent's.
            $table->unique(['id', 'firm_id']);
        });

        Schema::create('lfp_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('lfp_firms')->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('ic_number');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('phone');
            $table->string('email');
            $table->timestamps();

            $table->unique('user_id');
            $table->unique(['firm_id', 'ic_number']);
            $table->unique(['id', 'firm_id']);

            $table->foreign(['user_id', 'firm_id'])
                ->references(['id', 'firm_id'])
                ->on('lfp_users')
                ->cascadeOnDelete();
        });

        Schema::create('lfp_user_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('lfp_firms')->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->foreignId('profile_id');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('address3')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();

            $table->foreign(['user_id', 'firm_id'])
                ->references(['id', 'firm_id'])
                ->on('lfp_users')
                ->cascadeOnDelete();

            $table->foreign(['profile_id', 'firm_id'])
                ->references(['id', 'firm_id'])
                ->on('lfp_user_profiles')
                ->cascadeOnDelete();
        });

        // Reference data is deliberately NOT firm-scoped: role codes are defined
        // by the application, not by tenants. Give this a firm_id only if firms
        // must be able to define their own roles.
        Schema::create('lfp_ref_user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_code')->unique();
            $table->string('role_name');
            $table->string('role_description')->nullable();
            $table->timestamps();
        });

        // Keyed by email alone, which is only correct while lfp_users.email is
        // globally unique. See the note in the accompanying discussion if you
        // move to per-firm email uniqueness.
        Schema::create('lfp_user_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('lfp_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lfp_sessions');
        Schema::dropIfExists('lfp_user_password_reset_tokens');
        Schema::dropIfExists('lfp_ref_user_roles');
        Schema::dropIfExists('lfp_user_address');
        Schema::dropIfExists('lfp_user_profiles');
        Schema::dropIfExists('lfp_users');
    }
};
