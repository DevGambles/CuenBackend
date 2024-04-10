<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvMonitoringTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_monitoring', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('hash_map', 200)->nullable();
            $table->datetime('date_start');
            $table->datetime('date_deadline');
            $table->boolean('state')->default(0);

            //--- Foreign key ---//
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('cv_process');
            $table->integer('type_monitoring_id')->unsigned();
            $table->foreign('type_monitoring_id')->references('id')->on('cv_type_monitoring');
            $table->integer('user_id')->unsigned()->nullable(true);
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('user_id_creator')->unsigned();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_monitoring');
    }

}
