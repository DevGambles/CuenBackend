<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinancierLoadExcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_financier_load_excels', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('detail_json');
            $table->integer('type')->unsigned();//1 Carga exitosa  2 disponible no alcanza 3 001 4 basura
            $table->string('value')->nullable(true);//0 primera validacion se encontro basura  1 Carga exitosa  2 disponible no alcanza 3 codigo no existe 4 001 5 basura
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('cv_financier_load_excels');
    }
}
