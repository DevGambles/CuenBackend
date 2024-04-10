<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBasinTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_basin', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');

            // Llaves foraneas
            $table->integer('municipality_id')->unsigned();
            $table->foreign('municipality_id')->references('id')->on('cv_municipality');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_basin');
    }

}
