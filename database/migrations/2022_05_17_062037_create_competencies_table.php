<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_competencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->longText('description');
            $table->bigInteger('subject_id');
            $table->bigInteger('subject_component_id')->nullable();
            $table->bigInteger('grade_level_id');
            $table->bigInteger('week_id')->nullable();
            $table->bigInteger('period_id')->nullable();
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
        Schema::dropIfExists('tbl_competencies');
    }
}
