<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinancierActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_financier_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');

            $table->integer('activity_id')->unsigned();
            $table->foreign('activity_id')->references('id')->on('cv_project_activity');
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
        Schema::dropIfExists('cv_financier_actions');
    }
}
