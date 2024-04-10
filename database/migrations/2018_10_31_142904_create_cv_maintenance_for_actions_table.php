<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMaintenanceForActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_maintenance_for_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('manteniments_id')->unsigned();
            $table->foreign('manteniments_id')->references('id')->on('cv_actions');
            $table->integer('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('cv_budget_price_material');
            $table->integer('actions_id')->unsigned();
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
        Schema::dropIfExists('cv_maintenance_for_actions');
    }
}
