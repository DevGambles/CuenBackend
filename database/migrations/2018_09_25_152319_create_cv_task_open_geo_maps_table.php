<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskOpenGeoMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_task_open_geo_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('mapjson');
            $table->integer('type')->unsigned();//1 tarea hidrico //2erosivo //3psa

            $table->integer('task_open_id')->unsigned();
            $table->foreign('task_open_id')->references('id')->on('cv_task_open');
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
        Schema::dropIfExists('cv_task_open_geo_maps');
    }
}
