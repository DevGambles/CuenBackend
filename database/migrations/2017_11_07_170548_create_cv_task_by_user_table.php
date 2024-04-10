<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskByUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_by_user', function (Blueprint $table) {
            $table->increments('id');

            // --- Llaves foraneas --- //
            $table->integer('user_id')->unsigned();
            $table->integer('task_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('cv_task');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_by_user');
    }

}
