<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds text_answer to store free-text responses for writing questions.
     * For MCQ/typing questions, this remains null; choice_id is used instead.
     * For writing questions, choice_id is null; text_answer stores the user's text.
     */
    public function up(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->text('text_answer')->nullable()->after('choice_id');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropColumn('text_answer');
        });
    }
};
