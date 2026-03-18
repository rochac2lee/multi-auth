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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('name');
            $table->foreignId('country_id')
                ->nullable()
                ->after('email')
                ->constrained('countries')
                ->nullOnDelete();
            $table->string('photography_studio')->nullable()->after('country_id');
            $table->string('surname')->nullable()->after('photography_studio');
            $table->string('instagram')->nullable()->after('surname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn([
                'avatar',
                'photography_studio',
                'surname',
                'instagram',
            ]);
        });
    }
};
