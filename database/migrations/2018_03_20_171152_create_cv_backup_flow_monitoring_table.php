<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupFlowMonitoringTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_backup_flow_monitoring', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('monitoring_id')->nullable(true);
            $table->mediumText('info_monitoring')->nullable(true);
            $table->mediumText('info_flow_monitoring')->nullable(true);
            $table->mediumText('info_monitoring_points')->nullable(true);
            $table->mediumText('info_monitoring_images')->nullable(true);
            $table->mediumText('info_monitoring_comments')->nullable(true);
            $table->mediumText('info_monitoring_form_stard')->nullable(true);
            $table->mediumText('info_monitoring_form_tracing_predial')->nullable(true);
            $table->mediumText('info_monitoring_form_certificate_maintenance_vegetable')->nullable(true);
            $table->mediumText('info_monitoring_form_evaluation_provider')->nullable(true);
            $table->integer('from')->nullable(true);
            $table->integer('to')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_backup_flow_monitoring');
    }

}
