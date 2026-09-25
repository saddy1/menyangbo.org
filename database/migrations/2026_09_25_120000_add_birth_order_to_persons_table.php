<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // Place among same-gender siblings: 1 = जेठो/जेठी, 2 = माहिलो/माहिली …
            $table->unsignedTinyInteger('birth_order')->nullable()->after('birth_date_bs');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('birth_order');
        });
    }
};
