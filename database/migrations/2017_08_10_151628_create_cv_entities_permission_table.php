<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvEntitiesPermissionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_entities_permission', function (Blueprint $table) {
            $table->increments('id');

            //Llaves foraneas
            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('cv_permission');
            $table->integer('entities_id')->unsigned();
            $table->foreign('entities_id')->references('id')->on('cv_entities');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_entities_permission');
    }

}
