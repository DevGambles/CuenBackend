<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMonitoringCommentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_monitoring_comment', function (Blueprint $table) {

            $table->increments('id');
            $table->string('description', 250);
            $table->integer('monitoring_id')->unsigned()->nullable(true);
            $table->foreign('monitoring_id')->references('id')->on('cv_monitoring');
            $table->integer('monitoring_point_id')->unsigned()->nullable(true);
            $table->foreign('monitoring_point_id')->references('id')->on('cv_monitoring_points');
            $table->integer('user_id')->unsigned()->nullable(true);
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_monitoring_comment');
    }

}
