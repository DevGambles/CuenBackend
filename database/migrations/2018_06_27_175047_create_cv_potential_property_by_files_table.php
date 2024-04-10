<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialPropertyByFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_potential_property_by_files', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type_file', ["cc", "ct"]);

            //--- Llaves foraneas ---//
            $table->integer('potential_property_id')->unsigned();
            $table->integer('file_id')->unsigned();

            $table->foreign('potential_property_id')->references('id')->on('cv_potential_properties')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('cv_files');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_potential_property_by_files');
    }

}
