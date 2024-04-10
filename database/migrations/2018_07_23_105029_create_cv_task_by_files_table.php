<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskByFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_task_by_files', function (Blueprint $table) {
            $table->increments('id');
            
            //--- foreign key ---//
            $table->integer('task_id')->unsigned()->nullable(true);
            $table->integer('task_sub_type_id')->unsigned()->nullable(true);
            $table->integer('task_type_file_id')->unsigned()->nullable(true);
            $table->integer('file_id')->unsigned()->nullable(true);
            

            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->foreign('task_sub_type_id')->references('id')->on('cv_task_sub_type');
            $table->foreign('task_type_file_id')->references('id')->on('cv_type_file');
            $table->foreign('file_id')->references('id')->on('cv_files');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_task_by_files');
    }

}
