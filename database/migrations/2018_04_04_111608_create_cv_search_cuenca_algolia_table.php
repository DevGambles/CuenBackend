<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvSearchCuencaAlgoliaTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_search_cuenca_algolia', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name', 300);
            $table->string('description_short', 400);
            $table->mediumText('description');
            $table->string('type', 180);
            $table->integer('entity_id'); /* Id que se genero por la entidad en cada registro */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_search_cuenca_algolia');
    }

}
