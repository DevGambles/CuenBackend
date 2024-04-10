<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvFileByPayProcessContractorTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_file_by_pay_process_contractor', function (Blueprint $table) {
            $table->increments('id');
            // Llaves foraneas
            $table->integer('file_id')->unsigned();
            $table->integer('pay_id')->unsigned();
            $table->enum('type', ['parafiscales', 'certificado', 'factura']);

            $table->foreign('file_id')->references('id')->on('cv_files');
            $table->foreign('pay_id')->references('id')->on('cv_pay_process_contractor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_file_by_pay_process_contractor');
    }

}
