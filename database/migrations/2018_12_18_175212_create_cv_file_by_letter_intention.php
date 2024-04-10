<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFileByLetterIntention extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_file_by_letter_intention', function (Blueprint $table) {
            $table->increments('id');

            // Llaves foraneas 
            $table->integer('file_id')->unsigned()->nullable();
            $table->integer('letter_intention_id')->unsigned();

            $table->foreign('file_id')->references('id')->on('cv_files');
            $table->foreign('letter_intention_id')->references('id')->on('cv_letter_intention');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_file_by_letter_intention');
    }

}
