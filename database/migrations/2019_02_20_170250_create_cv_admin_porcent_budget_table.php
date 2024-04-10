<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvAdminPorcentBudgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_admin_porcent_budget', function (Blueprint $table) {
            $table->increments('id');
            // Llaves foraneas
            $table->integer('administration')->default(20);
            $table->integer('utility')->default(5);
            $table->integer('iva')->default(19);
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
        Schema::dropIfExists('cv_admin_porcent_budget');
    }

}
