<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds assessment_type (nullable) to distinguish between:
     *   - null        → normal lesson
     *   - 'pretest'   → pretest for its level
     *   - 'posttest'  → posttest for its level
     *
     * Replaces the old boolean is_assessment flag with a more
     * expressive, contextual field.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // nullable string; validated to pretest|posttest|null at the app layer
            $table->string('assessment_type')->nullable()->after('is_assessment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('assessment_type');
        });
    }
};
