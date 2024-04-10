<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPoolTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_pool', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->string('consecutive', 200);

            $table->integer('contract_id')->unsigned();
            $table->foreign('contract_id')->references('id')->on('cv_type_contract_bolsas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_pool');
    }

}
