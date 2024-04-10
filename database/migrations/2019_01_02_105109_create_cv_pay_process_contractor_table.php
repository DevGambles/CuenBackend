<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPayProcessContractorTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_pay_process_contractor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('value');
            $table->string('state')->default(0);
            $table->string('approved')->default(0);
            $table->enum('sub_type', ['Solicitar pago', 'Esperando aprobación de pago', 'Rechazo de pago por financiero', 'Aprobación del pago por financiero']);
            $table->string('from_user');
            $table->string('to_user');
            $table->integer('pool_id')->unsigned();
            $table->foreign('pool_id')->references('id')->on('cv_pool');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_pay_process_contractor');
    }

}
