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
        Schema::table('tbl_student_ecdcs', function (Blueprint $table): void {
            $table->string('score', 1)->default('*')->change();
            $table->string('present_type', 1)->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_student_ecdcs', function (Blueprint $table): void {
            $table->dropColumn('present_type');
            $table->integer('score')->default(0)->change();
        });
    }
};
