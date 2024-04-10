<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPsaBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_psa_budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('month')->nullable(true);
            $table->string('value_month')->nullable(true);
            $table->string('value_total')->nullable(true);
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
        Schema::dropIfExists('cv_psa_budgets');
    }
}
