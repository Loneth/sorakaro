<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pretest_attempt_id')->nullable()->constrained('attempts')->nullOnDelete();
            $table->foreignId('posttest_attempt_id')->nullable()->constrained('attempts')->nullOnDelete();
            $table->enum('status', [
                'not_started',
                'pretest_done',
                'guidebook_done',
                'posttest_done',
                'completed',
            ])->default('not_started');
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->smallInteger('improvement')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
