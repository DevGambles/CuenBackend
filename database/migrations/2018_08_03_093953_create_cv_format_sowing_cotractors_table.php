<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFormatSowingCotractorsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_format_sowing_cotractors', function (Blueprint $table) {
            $table->increments('id');

            $table->mediumText('form_sowing')->nullable(); //form
            $table->mediumText('hash')->nullable();

            $table->integer('user_id')->unsigned(); //contratista
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_format_sowing_cotractors');
    }

}
