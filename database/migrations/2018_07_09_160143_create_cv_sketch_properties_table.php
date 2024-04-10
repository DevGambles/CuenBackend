<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvSketchPropertiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_sketch_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('info_json_general')->nullable(true);

            //Llaves foraneas
            $table->integer('property_id')->unsigned()->nullable(true);
            $table->foreign('property_id')->references('id')->on('cv_property');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_sketch_properties');
    }

}
