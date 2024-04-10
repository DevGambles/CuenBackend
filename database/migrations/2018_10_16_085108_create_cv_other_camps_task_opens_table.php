<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvOtherCampsTaskOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_other_camps_task_opens', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('formjson');
            $table->integer('type')->unsigned()->nullable();//1 hidrico
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')->references('id')->on('cv_task_open');
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
        Schema::dropIfExists('cv_other_camps_task_opens');
    }
}
