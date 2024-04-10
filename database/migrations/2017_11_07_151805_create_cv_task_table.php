<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_task', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->datetime('date_start')->nullable(true);
            $table->datetime('date_end')->nullable(true);
            $table->boolean('option_date')->default(false);
            $table->boolean('state')->default(false);

            // Llaves foraneas
            $table->integer('task_type_id')->unsigned()->nullable(true);
            $table->integer('task_status_id')->unsigned();
            $table->integer('task_sub_type_id')->unsigned()->nullable(true);
            $table->integer('property_id')->unsigned()->nullable(true);

            $table->foreign('task_type_id')->references('id')->on('cv_task_type');
            $table->foreign('task_status_id')->references('id')->on('cv_task_status');
            $table->foreign('task_sub_type_id')->references('id')->on('cv_task_sub_type');
            $table->foreign('property_id')->references('id')->on('cv_property');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_task');
    }

}
