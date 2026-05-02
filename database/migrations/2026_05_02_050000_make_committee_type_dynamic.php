<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE committees MODIFY type VARCHAR(100) NOT NULL DEFAULT 'central'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE committees MODIFY type ENUM('central', 'district', 'local') NOT NULL DEFAULT 'central'");
    }
};
