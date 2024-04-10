<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('name');
            $table->string('extension');
            $table->string('url');
            $table->integer('pool_id')->unsigned();
            $table->integer('type_contract_bolsa_id')->unsigned();
            $table->integer('type_file_contract')->unsigned();

            $table->foreign('pool_id')->references('id')->on('cv_pool');
            $table->foreign('type_contract_bolsa_id')->references('id')->on('cv_type_contract_bolsas');
            $table->foreign('type_file_contract')->references('id')->on('cv_type_file_contracts');
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
        Schema::dropIfExists('cv_contracts');
    }
}
