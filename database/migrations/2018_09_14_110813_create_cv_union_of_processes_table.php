<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvUnionOfProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_union_of_processes', function (Blueprint $table) {
            $table->increments('id');
            //--- foreign key ---//
            $table->integer('process_father_id')->unsigned();
            $table->integer('process_son_id')->unsigned();
            $table->foreign('process_father_id')->references('id')->on('cv_process');
            $table->foreign('process_son_id')->references('id')->on('cv_process');

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
        Schema::dropIfExists('cv_union_of_processes');
    }
}
