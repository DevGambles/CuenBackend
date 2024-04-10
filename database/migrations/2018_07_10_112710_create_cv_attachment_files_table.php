<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvAttachmentFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_attachment_files', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->integer('state_delet')->default(0);

            //--- foreign key ---//
            $table->integer('file_id')->unsigned()->nullable(true);
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
        Schema::dropIfExists('cv_attachment_files');
    }

}
