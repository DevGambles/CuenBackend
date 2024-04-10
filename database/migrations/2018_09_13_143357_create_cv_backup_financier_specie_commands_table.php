<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupFinancierSpecieCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_backup_financier_specie_commands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->unsigned();
            $table->integer('last')->unsigned();//inversion
            $table->integer('id_save')->unsigned();
            $table->integer('type_table_save')->unsigned(); //1 contri excel 2 specie
            //data contribution
            $table->string('cuantity')->nullable();
            $table->string('dedication')->nullable();
            $table->string('unit_measurement')->nullable();
            $table->string('cuantity_measurement')->nullable();
            $table->string('benefit_factor')->nullable();
            $table->string('value_unit')->nullable();
            $table->string('code')->nullable(true);
            $table->integer('contributions_id')->nulleable(true);
            //data specie
            $table->integer('add_used')->nulleable(true);
            $table->string('price_used')->nulleable(true);
            $table->integer('cv_contribution_species')->nulleable(true);
            //data global
            $table->integer('financier_detail_id')->unsigned();
            $table->integer('associated_id')->unsigned();
            $table->integer('user_id')->unsigned();
            //command and control
            $table->string('paid_last');
            $table->string('paid_new');
            $table->string('balance_last');
            $table->string('balance_new');
            $table->string('committed_last');
            $table->string('committed_new');
            $table->string('committed_balance_last');
            $table->string('committed_balance_new');
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
        Schema::dropIfExists('cv_backup_financier_specie_commands');
    }
}
