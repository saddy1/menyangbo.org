<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_change_requests', function (Blueprint $table) {
            $table->id();

            // Can be null for "new person" requests, but for now we use existing person
            $table->foreignId('person_id')->nullable()->constrained('persons')->nullOnDelete();

            $table->enum('type', ['mark_deceased','add_child','update_profile'])->index();
            $table->json('payload'); // store request data

            $table->enum('status', ['pending','approved','rejected'])->default('pending')->index();

            $table->string('submitted_name', 255)->nullable();
            $table->string('submitted_email', 255)->nullable();
            $table->string('submitted_mobile', 50)->nullable();
            $table->text('submitted_note')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_change_requests');
    }
};
