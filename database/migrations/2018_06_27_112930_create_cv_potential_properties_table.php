<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialPropertiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_potential_properties', function (Blueprint $table) {

            $table->increments('id');
            //--- Latitud y longuitud ---//
            $table->string('property_name')->nullable(true);
            $table->string('main_coordinate')->nullable(true);
            //--- Saber si el predio potencial debe ser creado o editado ---//
            $table->boolean('check_state')->default(false);
            $table->boolean('property_psa')->default(false);
            //--- Foreign keys ---//
            $table->integer('potential_sub_type_id')->unsigned()->nullable();
            $table->foreign('potential_sub_type_id')->references('id')->on('cv_potential_sub_types');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_potential_properties');
    }

}
