<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTaskOpenBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_task_open_budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->unsigned();
            $table->integer('amount')->unsigned();
            $table->integer('task_open_id')->unsigned();
            $table->integer('associated_contributions_id')->unsigned();

            //--- Llaves foraneas ---//
            $table->foreign('task_open_id')->references('id')->on('cv_task_open');
            $table->foreign('associated_contributions_id')->references('id')->on('cv_associated_contributions');
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
        Schema::dropIfExists('cv_task_open_budgets');
    }
}
