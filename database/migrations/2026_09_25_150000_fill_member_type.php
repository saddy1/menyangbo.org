<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Fill सदस्यको प्रकार for everyone who has none:
     *   male → दाजुभाइ, female → दिदीबहिनी,
     *   female with a spouse and no parent in the family (married in) → बुहारी.
     * Values an admin already set are left alone.
     */
    public function up(): void
    {
        $empty = fn ($q) => $q->where(fn ($w) => $w->whereNull('member_type')->orWhere('member_type', '')->orWhere('member_type', 'दाजुभाइ दिदीबहिनी'));

        $married = DB::table('unions')->select('spouse1_id as id')->union(DB::table('unions')->select('spouse2_id as id'));
        $hasParent = DB::table('parent_child_edges')->select('child_id');

        $empty(DB::table('persons')->where('gender', 'female'))
            ->whereIn('id', $married)->whereNotIn('id', $hasParent)
            ->update(['member_type' => 'बुहारी']);

        $empty(DB::table('persons')->where('gender', 'male'))->update(['member_type' => 'दाजुभाइ']);
        $empty(DB::table('persons')->where('gender', 'female'))->update(['member_type' => 'दिदीबहिनी']);

        \App\Http\Controllers\Admin\PeopleTableController::forgetCache();
    }

    public function down(): void
    {
        DB::table('persons')->whereIn('member_type', ['दाजुभाइ', 'दिदीबहिनी', 'बुहारी'])->update(['member_type' => null]);
        \App\Http\Controllers\Admin\PeopleTableController::forgetCache();
    }
};
