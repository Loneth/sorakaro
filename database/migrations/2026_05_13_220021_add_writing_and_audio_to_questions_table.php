<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds support for writing and listening question types:
     *
     * - accepted_answers : JSON array of acceptable text answers (writing type)
     *                      e.g. ["halo", "hai", "horas"]
     * - audio_path       : storage path to an audio file (listening mode)
     *                      stored as a file reference under storage/app/public
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // For writing questions: flexible accepted answers with synonym support
            $table->json('accepted_answers')->nullable()->after('explanation');

            // For listening questions: path to an audio clip in Laravel storage
            $table->string('audio_path')->nullable()->after('accepted_answers');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['accepted_answers', 'audio_path']);
        });
    }
};
