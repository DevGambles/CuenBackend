<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskByProcessTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_by_process', function (Blueprint $table) {
            $table->increments('id');

            // Llaves foraneas 
            $table->integer('task_id')->unsigned();
            $table->integer('process_id')->unsigned();

            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->foreign('process_id')->references('id')->on('cv_process');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_by_process');
    }

}
