<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvRoleEntitiesPermissionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_role_entities_permission', function (Blueprint $table) {
            $table->increments('id');

            //Llaves foraneas
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('cv_role');
            $table->integer('entities_permission_id')->unsigned();
            $table->foreign('entities_permission_id')->references('id')->on('cv_entities_permission');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_role_entities_permission');
    }

}
