<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskOpenTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task_open', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->datetime('date_start')->nullable(true);
            $table->datetime('date_end')->nullable(true);
            $table->boolean('option_date')->default(false);
            $table->boolean('state')->default(false);

            // Llaves foraneas
            $table->integer('task_status_id')->unsigned();
            $table->integer('property_id')->unsigned()->nullable(true);
            $table->integer('process_id')->unsigned();
            $table->integer('task_open_sub_type_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('task_status_id')->references('id')->on('cv_task_status');
            $table->foreign('property_id')->references('id')->on('cv_property');
            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->foreign('task_open_sub_type_id')->references('id')->on('cv_task_open_sub_types');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task_open');
    }

}
