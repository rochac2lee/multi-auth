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
        Schema::table('login_tokens', function (Blueprint $table) {
            $table->foreignUuid('app_id')->nullable()->after('email')->constrained('apps')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_tokens', function (Blueprint $table) {
            $table->dropForeign(['app_id']);
        });
    }
};
