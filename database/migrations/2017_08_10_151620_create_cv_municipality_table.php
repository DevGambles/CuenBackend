<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMunicipalityTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_municipality', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            //Llaves foraneas
            $table->integer('departament_id')->unsigned();
            $table->foreign('departament_id')->references('id')->on('cv_departaments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_municipality');
    }

}
