<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialPropertyPollsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    //Encuesta
    public function up() {
        Schema::create('cv_potential_property_polls', function (Blueprint $table) {
            $table->increments('id');

            //--- Información general y especifica de la encuesta ---//
            $table->mediumText('info_json_general')->nullable(true);
            //--- Llaves foraneas ---//
            $table->integer('potential_property_id')->unsigned()->nullable(true);
            $table->foreign('potential_property_id')->references('id')->on('cv_potential_properties')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_potential_property_polls');
    }

}
