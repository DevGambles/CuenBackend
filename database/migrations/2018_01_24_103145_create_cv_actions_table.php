<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvActionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_actions', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->string('color');
            $table->integer('good_practicess')->unsigned()->default(0);
            $table->string('color_fill')->nullable();
            $table->enum('type', ['accion', 'punto', 'area']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_actions');
    }

}
