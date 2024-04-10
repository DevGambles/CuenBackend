<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('names')->nullable();
            $table->string('last_names')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('state')->default(FALSE); //--- Los usuarios son creados por defecto con FALSE y cambia a TRUE cuando han sido eliminados ---/
            $table->rememberToken();

            //Llaves foraneas
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('cv_role');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('users');
    }

}
