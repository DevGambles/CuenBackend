<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialByCommentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_potential_by_comment', function (Blueprint $table) {

            $table->increments('id');
            // Llaves foraneas 
            $table->integer('comment_id')->unsigned()->nullable();
            $table->integer('potential_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('potential_sub_type_id')->unsigned()->nullable();

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('potential_id')->references('id')->on('cv_potential_properties')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('potential_sub_type_id')->references('id')->on('cv_potential_sub_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_potential_by_comment');
    }

}
