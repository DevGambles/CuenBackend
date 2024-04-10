<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFormEvaluationProviderTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_form_evaluation_provider', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumtext('form');
            $table->string('contract_number');
            $table->string('provider_name');
            $table->string('nit_provider');
            $table->string('evaluation_period');
            $table->string('score', 500);
            $table->boolean('is_approved');
            $table->string('category');
            $table->string('comments')->nullable();

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

        Schema::dropIfExists('cv_form_evaluation_provider');
    }

}
