<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove skill_category from questions.
     *
     * The feature was removed because it added complexity without adding
     * meaningful value to the current Guided Learning flow.
     * The column is dropped cleanly — it was always nullable, so no data
     * migration is needed.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('skill_category');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('skill_category')->nullable()->after('type');
        });
    }
};
