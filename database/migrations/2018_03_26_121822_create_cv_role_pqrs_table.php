<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvRolePqrsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_role_pqrs', function (Blueprint $table) {

            $table->increments('id');

            //--- Foreign key ---//

            $table->integer('dependencies_role_id')->unsigned();
            $table->foreign('dependencies_role_id')->references('id')->on('cv_role');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_role_pqrs');
    }

}
