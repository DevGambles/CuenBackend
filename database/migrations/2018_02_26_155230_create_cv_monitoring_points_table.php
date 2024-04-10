<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMonitoringPointsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_monitoring_points', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);

            //--- Foreing keys ---//
            $table->integer('monitoring_id')->unsigned()->nullable(true);
            $table->foreign('monitoring_id')->references('id')->on('cv_monitoring');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_monitoring_points');
    }

}
