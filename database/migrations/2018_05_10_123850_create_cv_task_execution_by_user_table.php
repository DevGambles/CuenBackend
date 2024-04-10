<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskExecutionByUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_execution_by_user', function (Blueprint $table) {
            $table->increments('id');

            // --- Llaves foraneas --- //
            $table->integer('user_id')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->integer('pool_contractor_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('cv_task_execution');
            $table->foreign('pool_contractor_id')->references('id')->on('cv_pool_actions_by_user_contractor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_execution_by_user');
    }

}
