<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    // not_listed was already used by the "not listed" form but missing from the enum
    public function up(): void
    {
        DB::statement("ALTER TABLE person_change_requests MODIFY `type` ENUM('mark_deceased','add_child','update_profile','add_union','not_listed','link_parent') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE person_change_requests MODIFY `type` ENUM('mark_deceased','add_child','update_profile','add_union') NOT NULL");
    }
};
