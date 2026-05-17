<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan duplicate lesson yang memiliki level_id dan assessment_type sama
        $duplicates = \Illuminate\Support\Facades\DB::table('lessons')
            ->select('level_id', 'assessment_type', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('level_id', 'assessment_type')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $lessons = \Illuminate\Support\Facades\DB::table('lessons')
                ->where('level_id', $duplicate->level_id)
                ->where('assessment_type', $duplicate->assessment_type)
                ->orderBy('created_at', 'desc')
                ->get();

            // Simpan yang paling baru, ubah assessment_type yang lebih lama agar tidak bentrok
            $lessons->shift();

            foreach ($lessons as $lesson) {
                \Illuminate\Support\Facades\DB::table('lessons')
                    ->where('id', $lesson->id)
                    ->update([
                        'assessment_type' => $lesson->assessment_type . '-dup-' . $lesson->id
                    ]);
            }
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->unique(['level_id', 'assessment_type'], 'lessons_level_assessment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_level_assessment_unique');
        });
    }
};
