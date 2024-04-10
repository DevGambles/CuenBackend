<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvUnforeseenTariffContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_unforeseen_tariff_contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('budget_contractor');
            $table->string('description')->nulleable(true);

            $table->integer('pool_id')->unsigned();
            $table->foreign('pool_id')->references('id')->on('cv_pool');
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
        Schema::dropIfExists('cv_unforeseen_tariff_contractors');
    }
}
