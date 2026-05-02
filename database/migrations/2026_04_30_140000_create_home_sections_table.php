<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section', 40);        // at_a_glance | timeline | key_figures | notes
            $table->string('title', 300);          // main label / name
            $table->string('subtitle', 300)->nullable(); // role badge / date range / label key
            $table->text('body')->nullable();      // description; newline-separated bullet points
            $table->string('color', 30)->nullable(); // key_figures: emerald/sky/rose/fuchsia/amber/teal
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
