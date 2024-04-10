<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvActionsByActivityTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_actions_by_activity', function (Blueprint $table) {

            $table->increments('id');

            // Llaves foraneas
            $table->integer('action_id')->unsigned();
            $table->integer('activity_id')->unsigned();

            $table->foreign('action_id')->references('id')->on('cv_actions');
            $table->foreign('activity_id')->references('id')->on('cv_project_activity');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_actions_by_activity');
    }

}
