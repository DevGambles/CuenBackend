<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvLetterIntentionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_letter_intention', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumtext('form_letter')->nullable();

            //--- Foreign key ---//
            $table->integer('process_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('type_id')->unsigned();

            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('type_id')->references('id')->on('cv_task_type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_letter_intention');
    }

}
