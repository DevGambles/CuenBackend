<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentHaschPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_comment_hasch_points', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hash_map')->nullable(true);
            $table->string('description', 250);
            $table->integer('task_id')->unsigned();
            $table->integer('type')->unsigned();//1 tareas real 2 tareas abiertas 3 tarea de ejecucion
            $table->integer('user_id')->unsigned();
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
        Schema::dropIfExists('cv_comment_hasch_points');
    }
}
