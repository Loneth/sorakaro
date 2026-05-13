<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The guidebook_items table already has audio_path as a string.
     * This migration makes it explicit that the field stores a Laravel
     * storage path (under storage/app/public/) pointing to an uploaded file.
     * No structural change needed — column already exists and is nullable.
     *
     * Keeping as a no-op but documented for traceability.
     */
    public function up(): void
    {
        // Column already exists from the original create_guidebook_items_table migration.
        // Ensure it is nullable (it should be by default, but we make it explicit).
        Schema::table('guidebook_items', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Nothing to revert — column was always nullable.
    }
};
