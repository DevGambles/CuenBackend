<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupTaskOpenAndEspecialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_backup_task_open_and_especials', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('info');
            $table->integer('type')->unsigned();//por el momento tipo 0
            $table->integer('task_open_id')->unsigned();
            $table->integer('to_subtype')->unsigned();
            $table->integer('to_user')->unsigned();
            $table->integer('go_subtype')->unsigned();
            $table->integer('go_user')->unsigned();
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
        Schema::dropIfExists('cv_backup_task_open_and_especials');
    }
}
