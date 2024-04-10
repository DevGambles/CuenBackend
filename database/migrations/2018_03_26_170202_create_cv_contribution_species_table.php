<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvContributionSpeciesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_contribution_species', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quantity');
            $table->string('description');
            $table->string('price_unit');
            $table->integer('used')->default(0);
            $table->integer('balance')->default(0);
            $table->integer('contributions_id')->unsigned();
            $table->foreign('contributions_id')->references('id')->on('cv_associated_contributions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_contribution_species');
    }

}
