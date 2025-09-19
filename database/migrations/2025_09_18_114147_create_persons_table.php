<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('given_name');
            $table->string('middle_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('display_name')->index();
            $table->enum('gender', ['male','female','other','unknown'])->default('unknown');
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->boolean('is_deceased')->default(false);
            $table->string('photo_path')->nullable();
            $table->longText('bio')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('persons');
    }
};
