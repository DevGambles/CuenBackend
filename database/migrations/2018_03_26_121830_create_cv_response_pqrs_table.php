<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvResponsePqrsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_response_pqrs', function (Blueprint $table) {

            $table->increments('id');

            $table->mediumtext('response_email_request_pqrs')->nullable();

            // --- Llaves foraneas --- //

            $table->integer('pqrs_id')->unsigned();
            $table->foreign('pqrs_id')->references('id')->on('cv_pqrs');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_response_pqrs');
    }

}
