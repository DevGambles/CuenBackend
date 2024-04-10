<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvContributionPerSharesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_contribution_per_shares', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('budget_id')->unsigned()->nullable();
            $table->foreign('budget_id')->references('id')->on('cv_budget');
            $table->integer('associated_id')->unsigned()->nullable();
            $table->foreign('associated_id')->references('id')->on('cv_associateds');
            $table->integer('task_id')->unsigned()->nullable();
            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_contribution_per_shares');
    }

}
