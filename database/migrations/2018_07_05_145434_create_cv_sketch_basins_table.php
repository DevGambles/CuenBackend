<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvSketchBasinsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_sketch_basins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_basin');
            $table->string('type_geometry');
            $table->polygon('regions_bounds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_sketch_basins');
    }

}
