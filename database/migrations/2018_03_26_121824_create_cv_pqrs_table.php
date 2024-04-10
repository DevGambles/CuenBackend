<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPqrsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_pqrs', function (Blueprint $table) {

            $table->increments('id');
            $table->string('id_card')->nullable();
            $table->string('name')->nullable();
            $table->string('email', 500)->nullable();
            $table->boolean('conservation_agreement_corporation')->nullable();
            $table->boolean('subscribe_agreement')->nullable();
            $table->boolean('state')->nullable()->default(0);
            $table->string('description', 1000)->nullable();

            //--- Foreign key ---//

            $table->integer('property_id')->unsigned()->nullable();
            $table->integer('dependencies_role_id')->unsigned();
            $table->integer('type_pqrs_id')->unsigned();

            $table->foreign('property_id')->references('id')->on('cv_property');
            $table->foreign('dependencies_role_id')->references('id')->on('cv_role_pqrs');
            $table->foreign('type_pqrs_id')->references('id')->on('cv_type_pqrs');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_pqrs');
    }

}
