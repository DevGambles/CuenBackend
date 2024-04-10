<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskExecutionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_execution', function (Blueprint $table) {

            $table->increments('id');
            $table->string('title');
            $table->string('description');
            $table->datetime('date_start')->nullable(true);
            $table->datetime('date_end')->nullable(true);

            //--- Llaves foraneas ---//
            $table->integer('pool_actions_contractor_id')->unsigned();
            $table->integer('task_status_id')->unsigned();
            $table->integer('task_open_sub_type_id')->unsigned();
            $table->foreign('pool_actions_contractor_id')->references('id')->on('cv_pool_actions_by_user_contractor');
            $table->foreign('task_status_id')->references('id')->on('cv_task_status');
            $table->foreign('task_open_sub_type_id')->references('id')->on('cv_task_open_sub_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_execution');
    }

}
