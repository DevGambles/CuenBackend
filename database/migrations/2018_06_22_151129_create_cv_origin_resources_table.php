<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvOriginResourcesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_origin_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('value');
            $table->integer('type_task'); //que tipo de accion es 1 activa 2 pasiva 3 buenas practicas
            //llaves foraneas
            $table->integer('budget_id')->unsigned()->nullable();
            $table->foreign('budget_id')->references('id')->on('cv_budget');
            $table->integer('process_id')->unsigned()->nullable();
            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_origin_resources');
    }

}
