<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_feedback_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feedback', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('contact')->nullable();   // phone / whatsapp etc.
            $t->text('description');             // required
            // tiny honeypot + meta (optional)
            $t->string('hp_field')->nullable();
            $t->string('ip', 64)->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('feedback'); }
};
