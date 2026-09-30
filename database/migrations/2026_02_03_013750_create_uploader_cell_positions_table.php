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
        Schema::create('tbl_uploader_cell_positions', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->string('academic_year')->nullable();
            $table->string('grade_level');
            $table->string('section');
            $table->string('total');
            $table->string('strand')->nullable();
            $table->string('lrn');
            $table->string('name');
            $table->string('gender');
            $table->string('birth_date');
            $table->integer('start');
            $table->boolean('is_shs')->default(0);
            $table->boolean('is_active')->default(1);
            $table->bigInteger('user_id')->references('id')->on('tbl_users')->onDelete('cascade');
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
        Schema::dropIfExists('tbl_uploader_cell_positions');
    }
};
