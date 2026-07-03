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
        Schema::create('tbl_student_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('item_number');
            $table->char('answer', 1);
            $table->boolean('is_correct');
            $table->bigInteger('student_id');
            $table->bigInteger('class_assessment_id');
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
        Schema::dropIfExists('tbl_student_answers');
    }
};
