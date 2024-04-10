<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupPotentialLetterPollsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_backup_potential_letter_polls', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('info')->nullable(true);

            /*
             * Campo para determinar si se actualizo la encuesta o la carta de intencion
             * 1. Encuesta: False
             * 2. Carta de intencion: True
             */
            $table->boolean('info_letter_or_poll')->nullable(true);

            $table->integer('potential_id')->nullable(true);
            $table->integer('user_id')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_backup_potential_letter_polls');
    }

}
