<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parent_child_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('persons')->cascadeOnDelete();
            $table->enum('relation_type', ['birth','adoption','step','guardianship'])->default('birth');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['parent_id','child_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('parent_child_edges');
    }
};
