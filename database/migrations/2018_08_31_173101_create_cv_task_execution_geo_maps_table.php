<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskExecutionGeoMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_task_execution_geo_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('mapjson');
            $table->integer('type')->unsigned();//1 envio de seguimiento 2 envio de sig

            $table->integer('task_execution_id')->unsigned();
            $table->foreign('task_execution_id')->references('id')->on('cv_task_execution');

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
        Schema::dropIfExists('cv_task_execution_geo_maps');
    }
}
