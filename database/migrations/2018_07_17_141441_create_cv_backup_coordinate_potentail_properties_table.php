<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupCoordinatePotentailPropertiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_backup_coordinate_potential_properties', function (Blueprint $table) {

            $table->increments('id'); 
            $table->string("coordinate", 50); //--- Coordenada anterior ---//
            $table->integer("user_id_edit"); //--- Usuario que actualizo la coordenada ---//
            $table->integer("potential_property_id"); //--- Predio potencial actualizado ---//
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_backup_coordinate_potential_properties');
    }

}
