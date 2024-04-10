<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvGoalsForContributionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_goals_for_contributions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit');
            $table->string('description');
            $table->integer('quantity');
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
        Schema::dropIfExists('cv_goals_for_contributions');
    }

}
