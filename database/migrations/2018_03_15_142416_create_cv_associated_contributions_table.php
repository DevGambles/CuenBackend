<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvAssociatedContributionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_associated_contributions', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('inversion')->nullable();
            $table->mediumText('inversion_origin')->nullable();
            $table->mediumText('inversion_species')->nullable();
            $table->mediumText('paid')->nullable(true);
            $table->mediumText('committed')->nullable(true);
            $table->mediumText('committed_balance')->nullable(true);
            $table->mediumText('balance')->nullable(true);
            $table->integer('year');
            $table->integer('type');
            $table->integer('associated_id')->unsigned();
            $table->foreign('associated_id')->references('id')->on('cv_associateds');
            $table->integer('project_activity_id')->unsigned();
            $table->foreign('project_activity_id')->references('id')->on('cv_project_activity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_associated_contributions');
    }

}
