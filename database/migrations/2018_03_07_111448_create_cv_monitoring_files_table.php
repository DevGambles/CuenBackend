<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMonitoringFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_monitoring_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('monitoring_point_id')->unsigned()->nullable(true);
            $table->foreign('monitoring_point_id')->references('id')->on('cv_monitoring_points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_monitoring_files');
    }

}
