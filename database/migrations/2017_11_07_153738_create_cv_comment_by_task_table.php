<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentByTaskTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_comment_by_task', function (Blueprint $table) {

            $table->increments('id');

            // Llaves foraneas 
            $table->integer('comment_id')->unsigned()->nullable();
            $table->integer('task_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('task_sub_type_id')->unsigned()->nullable();

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_sub_type_id')->references('id')->on('cv_task_sub_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_comment_by_task');
    }

}
