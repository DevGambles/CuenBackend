<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvComandExcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_comand_excels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('programas')->nullable(true);
            $table->string('projectos')->nullable(true);
            $table->string('actividades')->nullable(true);        
            $table->string('metas')->nullable(true);        
            $table->string('asociados')->nullable(true);        
            $table->string('inversion')->nullable(true);           
            $table->string('especie')->nullable(true);
            $table->string('pagado')->nullable(true);
            $table->string('comprometido')->nullable(true);         
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
        Schema::dropIfExists('cv_comand_excels');
    }
}
