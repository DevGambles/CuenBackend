<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskOpenBudgetSpeciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_task_open_budget_species', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_open_budgets_id')->unsigned();
            $table->integer('cantidad')->unsigned();
            $table->integer('contribution_species_id')->unsigned();


            $table->foreign('task_open_budgets_id')->references('id')->on('cv_task_open_budgets');
            $table->foreign('contribution_species_id')->references('id')->on('cv_contribution_species');
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
        Schema::dropIfExists('cv_task_open_budget_species');
    }
}
