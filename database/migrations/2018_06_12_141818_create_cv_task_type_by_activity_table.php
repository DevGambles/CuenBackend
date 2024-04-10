<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskTypeByActivityTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_type_by_activity', function (Blueprint $table) {

            $table->increments('id');

            // Llaves foraneas
            $table->integer('task_type_id')->unsigned();
            $table->integer('activity_id')->unsigned();

            $table->foreign('task_type_id')->references('id')->on('cv_task_type');
            $table->foreign('activity_id')->references('id')->on('cv_project_activity');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_type_by_activity');
    }

}
