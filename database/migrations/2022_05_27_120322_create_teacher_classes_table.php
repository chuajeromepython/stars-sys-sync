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
        Schema::create('tbl_teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('classroom_id');
            $table->bigInteger('teacher_id');
            $table->integer('subject_id');
            $table->boolean('advisory');
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
        Schema::dropIfExists('tbl_teacher_classes');
    }
};
