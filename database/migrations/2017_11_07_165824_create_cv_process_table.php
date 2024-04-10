<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvProcessTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_process', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            //para tipo de proceso abierto: erosivos, hidricos, comunicaciones.
            $table->string('type_process')->nullable();

            //--- Llave foranea logica para unir el procedimiento con el predio potencial ---//
            $table->integer('potential_property_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_process');
    }

}
