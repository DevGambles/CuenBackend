<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBudgetByBudgetContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_budget_by_budget_contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('budget_contractor');
            $table->string('price_contractor');
            $table->integer('budget_id')->unsigned();
            $table->foreign('budget_id')->references('id')->on('cv_budget');
            $table->integer('tariff_id')->unsigned();
            $table->foreign('tariff_id')->references('id')->on('cv_tariff_action_contractors');
            $table->integer('contractor_id')->unsigned(); //contratista
            $table->foreign('contractor_id')->references('id')->on('users');
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
        Schema::dropIfExists('cv_budget_by_budget_contractors');
    }
}
