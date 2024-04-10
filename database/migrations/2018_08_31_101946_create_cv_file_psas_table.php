<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFilePsasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_file_psas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('type')->unsigned();//1PSA 2 HIDRICO 3 EROSIVO 4 FINANCIERO
            $table->integer('state_delete')->default(false);
            $table->integer('user_id')->unsigned()->nullable(true);
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
        Schema::dropIfExists('cv_file_psas');
    }
}
