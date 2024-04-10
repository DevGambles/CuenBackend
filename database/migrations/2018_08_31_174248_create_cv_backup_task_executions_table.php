<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvBackupTaskExecutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_backup_task_executions', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('mapjson');
            $table->integer('type')->unsigned();//tipos de envio de mapa
            $table->integer('task_execution_id')->unsigned();
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
        Schema::dropIfExists('cv_backup_task_executions');
    }
}
