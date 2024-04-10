<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFilesFormComunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_files_form_comunications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('state_delete')->default(false);
            $table->integer('formsjson_id')->unsigned()->nullable(true);
            $table->foreign('formsjson_id')->references('id')->on('cv_communication_forms_jsons');
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
        Schema::dropIfExists('cv_files_form_comunications');
    }
}
