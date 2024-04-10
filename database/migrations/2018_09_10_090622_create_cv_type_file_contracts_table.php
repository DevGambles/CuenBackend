<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvTypeFileContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_type_file_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('type_contract_bolsa')->unsigned();

            $table->foreign('type_contract_bolsa')->references('id')->on('cv_type_contract_bolsas');
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
        Schema::dropIfExists('cv_type_file_contracts');
    }
}
