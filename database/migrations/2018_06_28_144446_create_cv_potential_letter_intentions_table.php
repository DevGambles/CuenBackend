<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialLetterIntentionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_potential_letter_intentions', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumtext('form_letter')->nullable();

            //--- Foreign key ---//
            $table->integer('user_id')->unsigned();

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
        Schema::dropIfExists('cv_potential_letter_intentions');
    }

}
