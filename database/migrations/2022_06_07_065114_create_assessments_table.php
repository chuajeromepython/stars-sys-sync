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
        Schema::create('tbl_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('date');
            $table->integer('number_of_items');
            $table->integer('assessment_type_id');
            $table->bigInteger('period_id');
            $table->bigInteger('grade_level_id');
            $table->bigInteger('subject_id');
            $table->bigInteger('teacher_id');
            $table->bigInteger('academic_year_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_assessments');
    }
};
