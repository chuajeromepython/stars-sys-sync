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
        Schema::create('tbl_ecdcs', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('classroom_id');
            $table->bigInteger('academic_year_id');
            $table->string('period');
            $table->bigInteger('teacher_id');
            $table->string('source')->comment('1-uploaded; 2-encoded');
            $table->string('date');
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
        Schema::dropIfExists('tbl_ecdcs');
    }
};
