<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvContractorBudgetDetailOriginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_contractor_budget_detail_origins', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('value');
            $table->mediumText('ultimate_committed')->nullable(true);
            $table->integer('budget_contractor_id')->unsigned()->nullable();
            $table->foreign('budget_contractor_id')->references('id')->on('cv_budget_by_budget_contractors');
            $table->integer('contribution_id')->unsigned()->nullable(); //comando y control
            $table->foreign('contribution_id')->references('id')->on('cv_associated_contributions');
            $table->integer('associated_id')->unsigned()->nullable();
            $table->foreign('associated_id')->references('id')->on('cv_associateds');
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
        Schema::dropIfExists('cv_contractor_budget_detail_origins');
    }
}
