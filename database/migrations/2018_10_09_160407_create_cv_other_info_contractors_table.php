<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvOtherInfoContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_other_info_contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('infojson');

            $table->integer('pool_id')->unsigned();
            $table->foreign('pool_id')->references('id')->on('cv_pool');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('cv_other_info_contractors');
    }
}
