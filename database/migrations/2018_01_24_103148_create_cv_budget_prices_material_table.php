<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBudgetPricesMaterialTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_budget_price_material', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->string('price');
            $table->enum('type', ['restauracion', 'alambre', 'broches', 'cerca viva', 'STARD']);
            $table->string('measurement');
            $table->string('last_name')->nullable(true)->default(null);
            
            //*** foreign key ***//
            $table->integer('unit_id')->unsigned()->nullable(true);
            $table->foreign('unit_id')->references('id')->on('cv_units');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_budget_price_material');
    }

}
