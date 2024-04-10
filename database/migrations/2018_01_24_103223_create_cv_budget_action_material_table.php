<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBudgetActionMaterialTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_budget_action_material', function (Blueprint $table) {

            $table->increments('id');

            //*** foreign key ***//
            $table->integer('action_id')->unsigned();
            $table->integer('budget_prices_material_id')->unsigned();
            $table->foreign('action_id')->references('id')->on('cv_actions');
            $table->foreign('budget_prices_material_id')->references('id')->on('cv_budget_price_material');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_budget_action_material');
    }

}
