<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Converte users.id de UUID para unsignedBigInteger para igualar ao id
     * numérico da tabela tb_fotografos (Selpics/youfocus).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Remover FKs que referenciam users (app_user é dropada inteira e recriada)
        Schema::dropIfExists('app_user');
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('app_user');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // sessions: alterar user_id para bigint
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL');
        }
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE oauth_access_tokens MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE oauth_auth_codes MODIFY user_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE oauth_device_codes MODIFY user_id BIGINT UNSIGNED NULL');
        }
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('app_user', function (Blueprint $table) {
            $table->foreignUuid('app_id')->constrained('apps');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('app_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('app_user');
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sessions MODIFY user_id CHAR(36) NULL');
            DB::statement('ALTER TABLE oauth_access_tokens MODIFY user_id CHAR(36) NULL');
            DB::statement('ALTER TABLE oauth_auth_codes MODIFY user_id CHAR(36) NOT NULL');
            DB::statement('ALTER TABLE oauth_device_codes MODIFY user_id CHAR(36) NULL');
        }
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('app_user', function (Blueprint $table) {
            $table->foreignUuid('app_id')->constrained('apps');
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
