<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFormMonitoringTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_form_monitoring', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumtext('form_stard')->nullable();
            $table->mediumtext('form_tracing_predial')->nullable();
            $table->mediumtext('form_certificate_maintenance_vegetable')->nullable();

            //--- Foreign key ---//
            $table->integer('monitoring_id')->unsigned();
            $table->foreign('monitoring_id')->references('id')->on('cv_monitoring');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_form_monitoring');
    }

}
