<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tbl_student_ecdcs')
            ->where('score', '0')
            ->update(['score' => '-']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The previous zero value is indistinguishable from a newly recorded
        // not-present status, so this normalization cannot be reversed safely.
    }
};
