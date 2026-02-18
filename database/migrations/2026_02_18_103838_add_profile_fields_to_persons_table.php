<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // Member identity
            $table->string('member_no', 50)->nullable()->index();       // DLUMP01
            $table->string('member_type', 100)->nullable();             // दाजु भाई
            $table->string('membership', 100)->nullable();              // सदस्यता
            $table->string('display_name_np', 255)->nullable();         // नेपाली नाम (optional)

            // Places / family
            $table->string('birth_place', 255)->nullable();             // ", Nepal"
            $table->string('father_name', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email', 255)->nullable();

            // Education / occupation
            $table->string('education', 255)->nullable();              // थाहा छैन
            $table->string('occupation', 255)->nullable();

            // Marriage
            $table->string('marital_status', 50)->nullable();           // विवाहित
            $table->string('marriage_date_bs', 20)->nullable();
            $table->date('marriage_date_ad')->nullable();

            // Extra profile
            $table->string('lineage', 255)->nullable();
            $table->string('family_type', 255)->nullable();
            $table->string('blood_group', 20)->nullable();
            $table->string('rashifal', 50)->nullable();
            $table->string('religion', 100)->nullable();
            $table->text('special_note')->nullable();

            // Death extra
            $table->string('death_place', 255)->nullable();
            $table->string('death_tithi', 50)->nullable();             // मृत्यु तिथी (string)
            $table->string('death_reason', 255)->nullable();

            // Admin tracking
            $table->string('registered_by', 100)->nullable();          // Self/Admin name
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn([
                'member_no','member_type','membership','display_name_np',
                'birth_place','father_name','mother_name','address','mobile','email',
                'education','occupation','marital_status','marriage_date_bs','marriage_date_ad',
                'lineage','family_type','blood_group','rashifal','religion','special_note',
                'death_place','death_tithi','death_reason','registered_by',
            ]);
        });
    }
};
