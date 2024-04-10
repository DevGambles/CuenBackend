<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommentByPayProcessContractorTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_comment_by_pay_process_contractor', function (Blueprint $table) {
            $table->increments('id');
            // Llaves foraneas
            $table->integer('comment_id')->unsigned();
            $table->integer('pay_id')->unsigned();

            $table->foreign('comment_id')->references('id')->on('cv_comment');
            $table->foreign('pay_id')->references('id')->on('cv_pay_process_contractor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_comment_by_pay_process_contractor');
    }

}
