<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTariffActionContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_tariff_action_contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('budget_contractor');
            //--- Llaves foraneas ---//
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('action_id')->unsigned();
            $table->foreign('action_id')->references('id')->on('cv_actions');

            $table->integer('budget_prices_material_id')->unsigned();
            $table->foreign('budget_prices_material_id')->references('id')->on('cv_budget_price_material');
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
        Schema::dropIfExists('cv_tariff_action_contractors');
    }
}
