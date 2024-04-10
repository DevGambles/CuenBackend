<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvImgBase64TaskByGeneralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_img_base64_task_by_generals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('file_id')->references('id')->on('cv_img_base_64_task');
            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_img_base64_task_by_generals');
    }
}
