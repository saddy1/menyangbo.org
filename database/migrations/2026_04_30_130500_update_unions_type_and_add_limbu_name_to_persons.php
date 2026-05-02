<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'display_name_limbu')) {
                $table->string('display_name_limbu', 255)->nullable()->after('display_name_np');
            }
        });

        DB::statement("ALTER TABLE unions MODIFY type ENUM('marriage','partnership','other','married','partner','divorced','widowed','separated') NOT NULL DEFAULT 'married'");
    }

    public function down(): void
    {
        DB::statement("UPDATE unions SET type = 'marriage' WHERE type IN ('married','divorced','widowed','separated')");
        DB::statement("UPDATE unions SET type = 'partnership' WHERE type = 'partner'");
        DB::statement("ALTER TABLE unions MODIFY type ENUM('marriage','partnership','other') NOT NULL DEFAULT 'marriage'");

        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'display_name_limbu')) {
                $table->dropColumn('display_name_limbu');
            }
        });
    }
};
