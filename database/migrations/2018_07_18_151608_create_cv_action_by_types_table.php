<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvActionByTypesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_action_by_types', function (Blueprint $table) {
            $table->increments('id');


            //--- Llaves foraneas ---//
            $table->integer('action_id')->unsigned();
            $table->foreign('action_id')->references('id')->on('cv_actions');

            $table->integer('type_id')->unsigned();
            $table->foreign('type_id')->references('id')->on('cv_action_types');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_action_by_types');
    }

}
