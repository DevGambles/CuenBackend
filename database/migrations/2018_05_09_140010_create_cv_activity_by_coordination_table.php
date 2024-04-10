<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvActivityByCoordinationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_activity_by_coordination', function (Blueprint $table) {
            $table->increments('id');

            // --- Llaves foraneas --- //
            $table->integer('activity_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('activity_id')->references('id')->on('cv_project_activity');
            $table->foreign('role_id')->references('id')->on('cv_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_activity_by_coordination');
    }

}
