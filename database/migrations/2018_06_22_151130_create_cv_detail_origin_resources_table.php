<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvDetailOriginResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_detail_origin_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('value');
            $table->mediumText('ultimate_committed')->nullable(true);
            $table->integer('associated_id')->unsigned()->nullable();
            $table->foreign('associated_id')->references('id')->on('cv_associateds');
            $table->integer('user_id')->unsigned()->nullable(); //usuario logueado quien creo el presupuesto
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('contribution_id')->unsigned()->nullable(); //comando y control
            $table->foreign('contribution_id')->references('id')->on('cv_associated_contributions');
            $table->integer('budget_id')->unsigned()->nullable();
            $table->foreign('budget_id')->references('id')->on('cv_budget');
            $table->integer('origin_id')->unsigned()->nullable();
            $table->foreign('origin_id')->references('id')->on('cv_origin_resources');
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
        Schema::dropIfExists('cv_detail_origin_resources');
    }
}
