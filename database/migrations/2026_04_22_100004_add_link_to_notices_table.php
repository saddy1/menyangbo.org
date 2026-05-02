<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->string('link')->nullable()->after('body');
            $table->string('link_text')->nullable()->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn(['link', 'link_text']);
        });
    }
};
