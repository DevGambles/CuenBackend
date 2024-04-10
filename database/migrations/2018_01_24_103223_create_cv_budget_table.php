<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBudgetTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_budget', function (Blueprint $table) {

            $table->increments('id');
            $table->string('value')->nullable(true);
            $table->string('length')->nullable(true);
            $table->string('hash_map')->nullable(true);
            $table->integer('good_practicess')->unsigned()->default(0);

            //*** Nuevas variables para calcular cada presupuesto independientemente ***//

            $table->integer('administration');
            $table->integer('utility');
            $table->integer('iva');

            //*** Foreign key ***//

            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')->references('id')->on('cv_task');
            $table->integer('action_material_id')->unsigned();
            $table->foreign('action_material_id')->references('id')->on('cv_budget_action_material');

            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_budget');
    }

}
