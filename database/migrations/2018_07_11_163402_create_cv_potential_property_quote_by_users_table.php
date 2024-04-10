<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvPotentialPropertyQuoteByUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_potential_property_quote_by_users', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('property_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('state')->default(false);

            //--- Llaves foraneas ---//
            $table->foreign('property_id')->references('id')->on('cv_potential_properties')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_potential_property_quote_by_users');
    }

}
