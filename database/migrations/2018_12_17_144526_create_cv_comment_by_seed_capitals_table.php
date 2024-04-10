<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentBySeedCapitalsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_comment_by_seed_capitals', function (Blueprint $table) {
            $table->increments('id');

            // Llaves foraneas 
            $table->integer('comment_id')->unsigned()->nullable();
            $table->integer('seed_capital_id')->unsigned();

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('seed_capital_id')->references('id')->on('seed_capitals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_comment_by_seed_capitals');
    }

}
