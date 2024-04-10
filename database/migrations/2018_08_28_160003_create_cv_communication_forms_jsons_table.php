<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvCommunicationFormsJsonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_communication_forms_jsons', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('formjson');
            $table->mediumText('hash')->nullable(true);
            $table->integer('type')->unsigned()->nullable();//1 task_open 2 task execution
            $table->integer('task_id')->unsigned();

            //--- Llaves foraneas ---//
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
        Schema::dropIfExists('cv_communication_forms_jsons');
    }
}
