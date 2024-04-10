<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupFlowTasksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_backup_flow_tasks', function (Blueprint $table) {

            $table->increments('id');
            $table->mediumText('info_task')->nullable(true);
            $table->mediumText('info_map_geo_json')->nullable(true);
            $table->mediumText('info_property')->nullable(true);
            $table->string('info_user_from');
            $table->string('info_user_to');
            $table->string('info_task_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_backup_flow_tasks');
    }

}
