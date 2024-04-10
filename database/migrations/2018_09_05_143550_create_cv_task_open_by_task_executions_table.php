<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskOpenByTaskExecutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_task_open_by_task_executions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_open')->unsigned();
            $table->foreign('task_open')->references('id')->on('cv_task_open');
            $table->integer('task_execution')->unsigned();
            $table->foreign('task_execution')->references('id')->on('cv_task_execution');
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
        Schema::dropIfExists('cv_task_open_by_task_executions');
    }
}
