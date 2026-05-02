<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->text('caption')->nullable()->after('title');
            $table->string('photo_path')->after('caption');
            $table->boolean('is_active')->default(true)->after('photo_path');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropColumn(['title', 'caption', 'photo_path', 'is_active', 'sort_order']);
        });
    }
};
