<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeedCapitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seed_capitals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codeCenter')->default('0');
            $table->string('nit')->default('0');
            $table->string('valueUsd')->default('0');
            $table->string('valueUsdOrigin')->default('0');
            $table->string('valueCo')->default('0');
            $table->string('valueCoOrigin')->default('0');

            $table->integer('cv_associateds_id')->unsigned();
            $table->foreign('cv_associateds_id')->references('id')->on('cv_associateds');


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
        Schema::dropIfExists('seed_capitals');
    }
}
