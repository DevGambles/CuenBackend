<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvOtherInfoMinutePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_other_info_minute_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('infojson');

            $table->integer('property_id')->unsigned();
            $table->foreign('property_id')->references('id')->on('cv_potential_properties');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cv_other_info_minute_properties');
    }
}
