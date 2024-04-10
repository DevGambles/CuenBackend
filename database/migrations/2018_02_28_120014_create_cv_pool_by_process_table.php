<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPoolByProcessTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_pool_by_process', function (Blueprint $table) {

            $table->increments('id');

            //--- foreign key ---//
            $table->integer('pool_id')->unsigned();
            $table->integer('process_id')->unsigned();
            $table->integer('budget_id')->unsigned()->nullable();
            $table->integer('task_open_id')->unsigned()->nullable();
            $table->foreign('pool_id')->references('id')->on('cv_pool');
            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->foreign('budget_id')->references('id')->on('cv_budget');
            $table->foreign('task_open_id')->references('id')->on('cv_task_open');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_pool_by_process');
    }

}
