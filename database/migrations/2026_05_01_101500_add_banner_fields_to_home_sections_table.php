<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
            $table->string('link_label', 120)->nullable()->after('image_path');
            $table->string('link_url', 500)->nullable()->after('link_label');
        });
    }

    public function down(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'link_label', 'link_url']);
        });
    }
};
