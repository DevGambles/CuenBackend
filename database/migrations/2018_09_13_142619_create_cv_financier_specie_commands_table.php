<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinancierSpecieCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_financier_specie_commands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('add_used');
            $table->string('price_used');
            $table->integer('financier_detail_id')->unsigned();
            $table->foreign('financier_detail_id')->references('id')->on('cv_financier_detail_codes');
            $table->integer('contributions_specie_id')->unsigned();
            $table->foreign('contributions_specie_id')->references('id')->on('cv_contribution_species');
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
        Schema::dropIfExists('cv_financier_specie_commands');
    }
}
