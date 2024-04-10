<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupFlowPotentialPropertiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_backup_flow_potential_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string("user_from");
            $table->string("user_to");
            $table->integer('potential_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_backup_flow_potential_properties');
    }

}
