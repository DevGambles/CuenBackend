<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFileForFormsTaskOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_file_for_forms_task_opens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('hash');
            $table->integer('state_delete')->default(false);
            $table->integer('type')->default(0);//0 hidrico 1erosivo 2psa
            $table->mediumText('description')->nullable(true);

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
        Schema::dropIfExists('cv_file_for_forms_task_opens');
    }
}
