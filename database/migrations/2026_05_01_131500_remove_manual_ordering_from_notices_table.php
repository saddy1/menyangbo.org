<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            if (Schema::hasColumn('notices', 'notice_time')) {
                $table->dropColumn('notice_time');
            }
            if (Schema::hasColumn('notices', 'notice_date')) {
                $table->dropColumn('notice_date');
            }
            if (Schema::hasColumn('notices', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            if (! Schema::hasColumn('notices', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (! Schema::hasColumn('notices', 'notice_date')) {
                $table->date('notice_date')->nullable();
            }
            if (! Schema::hasColumn('notices', 'notice_time')) {
                $table->time('notice_time')->nullable();
            }
        });
    }
};
