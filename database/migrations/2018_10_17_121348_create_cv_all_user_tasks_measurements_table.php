<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvAllUserTasksMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_all_user_tasks_measurements', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('send')->unsigned();//0=false 1= true
            // --- Llaves foraneas --- //
            $table->integer('user_id')->unsigned();
            $table->integer('task_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('cv_task');

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
        Schema::dropIfExists('cv_all_user_tasks_measurements');
    }
}
