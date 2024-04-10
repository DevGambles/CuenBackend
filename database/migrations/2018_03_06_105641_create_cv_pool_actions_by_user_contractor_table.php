<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPoolActionsByUserContractorTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_pool_actions_by_user_contractor', function (Blueprint $table) {

            $table->increments('id');

            //--- Foreign key ---//
            $table->integer('pool_by_process_id')->unsigned();
            $table->foreign('pool_by_process_id')->references('id')->on('cv_pool_by_process');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_pool_actions_by_user_contractor');
    }

}
