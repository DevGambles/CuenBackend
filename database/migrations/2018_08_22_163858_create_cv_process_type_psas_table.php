<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvProcessTypePsasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_process_type_psas', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('property_psa')->default(false);
            //--- Llaves foraneas ---//
            $table->integer('proccess_id')->unsigned();
            $table->foreign('proccess_id')->references('id')->on('cv_process');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_process_type_psas');
    }
}
