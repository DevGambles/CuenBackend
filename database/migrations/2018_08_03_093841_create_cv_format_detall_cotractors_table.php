<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFormatDetallCotractorsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_format_detall_cotractors', function (Blueprint $table) {
            $table->increments('id');

            $table->mediumText('form_contractor')->nullable(); //all form

            $table->integer('user_id')->unsigned()->nullable(); //contratista
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('task_id')->unsigned()->nullable(); //tarea abierta
            $table->foreign('task_id')->references('id')->on('cv_task_open');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_format_detall_cotractors');
    }

}
