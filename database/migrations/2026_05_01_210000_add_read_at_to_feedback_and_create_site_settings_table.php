<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('user_agent');
            }
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');

        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
