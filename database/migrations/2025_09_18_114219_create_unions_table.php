<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('unions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spouse1_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('spouse2_id')->constrained('persons')->cascadeOnDelete();
            $table->enum('type', ['marriage','partnership','other'])->default('marriage');
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['spouse1_id','spouse2_id','start_date'], 'unions_unique_duo');
        });
    }

    public function down(): void {
        Schema::dropIfExists('unions');
    }
};
