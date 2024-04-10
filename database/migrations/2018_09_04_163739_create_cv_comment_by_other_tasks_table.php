<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentByOtherTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_comment_by_other_tasks', function (Blueprint $table) {
            $table->increments('id');
            // Llaves foraneas
            $table->integer('comment_id')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('type')->unsigned();//1 tareas de ejecucion 2 tareas abiertas

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('cv_comment_by_other_tasks');
    }
}
