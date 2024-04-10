<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPropertyTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_property', function (Blueprint $table) {

            //Información principal del predio
            $table->increments('id');

            //Información general y especifica de la encuesta
            $table->mediumText('info_json_general')->nullable(true);

            //Latitud y longuitud
            $table->string('property_name')->nullable(true);
            $table->string('main_coordinate')->nullable(true);

            //Llaves foraneas
            $table->integer('property_correlation_id')->unsigned()->nullable(true);
            $table->foreign('property_correlation_id')->references('id')->on('cv_property_correlation');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_property');
    }

}
