<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvProgramByProjectTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_program_by_project', function (Blueprint $table) {

            $table->increments('id');

            //--- foreign key ---//
            $table->integer('project_id')->unsigned();
            $table->integer('program_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('cv_project');
            $table->foreign('program_id')->references('id')->on('cv_program');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_program_by_project');
    }

}
