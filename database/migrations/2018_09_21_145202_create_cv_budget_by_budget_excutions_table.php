<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBudgetByBudgetExcutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_budget_by_budget_excutions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shape_leng');
            $table->string('price_execution');
            //Foring key
            $table->integer('task_execution_id')->unsigned();
            $table->foreign('task_execution_id')->references('id')->on('cv_task_execution');
            $table->integer('budget_contractor_id')->unsigned();
            $table->foreign('budget_contractor_id')->references('id')->on('cv_budget_by_budget_contractors');
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
        Schema::dropIfExists('cv_budget_by_budget_excutions');
    }
}
