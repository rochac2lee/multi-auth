<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 200000');
    }

    public function down(): void
    {
        // Não é possível reverter de forma segura sem risco de colisão
    }
};
