<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFileOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_file_opens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('state_delete')->default(false);
            $table->integer('type')->default(0);//0 archivos normales //1certificado 2factura 3pago para fiscal 4/financiero exel 5exel hidrico 6 exel erosivo
            $table->mediumText('description')->nullable(true);
            $table->integer('task_open_id')->unsigned()->nullable(true);
            $table->foreign('task_open_id')->references('id')->on('cv_task_open');
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
        Schema::dropIfExists('cv_file_opens');
    }
}
