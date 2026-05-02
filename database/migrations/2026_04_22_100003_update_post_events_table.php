<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('post_events', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->string('location')->nullable()->after('description');
            $table->dateTime('event_date')->nullable()->after('location');
            $table->string('photo_path')->nullable()->after('event_date');
            $table->boolean('is_active')->default(true)->after('photo_path');
            $table->boolean('show_popup')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('post_events', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'location', 'event_date', 'photo_path', 'is_active', 'show_popup']);
        });
    }
};
