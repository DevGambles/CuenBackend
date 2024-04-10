<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFormatBySowingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_format_by_sowings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('detall_sowing_id')->unsigned()->nullable();
            $table->foreign('detall_sowing_id')->references('id')->on('cv_format_sowing_cotractors');

            $table->integer('detall_contractor_id')->unsigned()->nullable();
            $table->foreign('detall_contractor_id')->references('id')->on('cv_format_detall_cotractors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_format_by_sowings');
    }

}
