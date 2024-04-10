<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupContributionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('cv_backup_contributions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('info_inversion_to')->nullable(true);
            $table->integer('info_inversion_end')->nullable(true);
            $table->mediumText('inversion')->nullable(true);
            $table->string('inversion_species')->nullable();
            $table->integer('year')->nullable();
            $table->integer('type');
            $table->mediumText('other')->nullable(true);
            $table->integer('associated_contributions_id');
          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('cv_backup_contributions');
    }

}
