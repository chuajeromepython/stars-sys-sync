<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_classrooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('section_id');
            $table->bigInteger('grade_level_id');
            $table->bigInteger('academic_year_id');
            $table->bigInteger('school_id');
            $table->bigInteger('strand_id')->nullable();
            $table->bigInteger('course_id')->nullable();
            $table->bigInteger('track_id')->nullable();
            $table->bigInteger('semester_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_classrooms');
    }
};
