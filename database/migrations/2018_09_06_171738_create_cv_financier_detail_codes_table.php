<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinancierDetailCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_financier_detail_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');

            $table->integer('actiion_id')->unsigned();
            $table->foreign('actiion_id')->references('id')->on('cv_financier_actions');
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
        Schema::dropIfExists('cv_financier_detail_codes');
    }
}
