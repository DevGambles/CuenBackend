<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvProjectByActivityTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_project_by_activity', function (Blueprint $table) {

            $table->increments('id');

            //--- foreign key ---//
            $table->integer('project_id')->unsigned();
            $table->integer('activity_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('cv_project');
            $table->foreign('activity_id')->references('id')->on('cv_project_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_project_by_activity');
    }

}
