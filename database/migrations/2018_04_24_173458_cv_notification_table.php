<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CvNotificationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create('cv_notification', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->string('description', 500);
            $table->string('type', 200);
            $table->integer('entity_id'); /* Id que se genero por la entidad en cada registro */
            $table->integer('user_id'); /* Id del usuario que se le va asignar la notificacion */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('cv_notification');
    }

}
