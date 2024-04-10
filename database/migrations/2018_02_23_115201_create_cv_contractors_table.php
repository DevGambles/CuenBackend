<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvContractorsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_contractors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nit', 200)->nullable();
            $table->string('contract_number')->nullable();
            $table->enum('type_person', [1, 2]);
            $table->enum('type_identity', [1, 2]);
            $table->string('number_identity')->nullable();
            $table->string('object')->nullable();
            $table->string('total_value')->nullable();
            $table->string('way_to_pay')->nullable();
            $table->string('monthly_value')->nullable();
            $table->string('place_of_execution')->nullable();
            $table->date('initial_term')->nullable();
            $table->date('final_term')->nullable();
            $table->date('subscription_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->boolean('renew_guarantee')->nullable();
            $table->enum('guarantee', [1, 2, 3, 4])->nullable();

            /*
             * Data of guarantee
             * 
             * Cumplimiento, Buen manejo, Correcta inversión del anticipo, No Aplica
             */

            $table->string('number_modality')->nullable(); // ---> si la modalidad de contrato tiene un numero o es de tipo 'convocatoria pública número'
            $table->integer('user_from_id')->nullable(); // ---> El usuario que registro el registro
            //--- Llaves foraneas ---//
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('contract_modality_id')->unsigned();
            $table->foreign('contract_modality_id')->references('id')->on('cv_contractor_modalities');

            $table->integer('type_contract_id')->unsigned();
            $table->foreign('type_contract_id')->references('id')->on('cv_type_contracts');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_contractors');
    }

}
