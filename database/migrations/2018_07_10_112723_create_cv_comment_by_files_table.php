<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentByFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_comment_by_files', function (Blueprint $table) {

            $table->increments('id');

            //--- Llaves foraneas ---//
            $table->integer('comment_id')->unsigned();
            $table->integer('file_id')->unsigned();
            $table->integer('attachment_id')->unsigned();

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('file_id')->references('id')->on('cv_files');
            $table->foreign('attachment_id')->references('id')->on('cv_attachment_files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_comment_by_files');
    }

}
