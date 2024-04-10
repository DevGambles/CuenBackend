<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvProcessByActivityTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_process_by_activity', function (Blueprint $table) {

            $table->increments('id');

            //--- foreign key ---//
            $table->integer('process_id')->unsigned();
            $table->integer('project_activity_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->foreign('project_activity_id')->references('id')->on('cv_project_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_process_by_activity');
    }

}
