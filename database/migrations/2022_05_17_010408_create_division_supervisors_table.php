<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDivisionSupervisorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_division_supervisors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('status');
            $table->bigInteger('user_id');
            $table->bigInteger('division_id');
            $table->string('email');
            $table->string('subject_id');
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
        Schema::dropIfExists('tbl_division_supervisors');
    }
}
