<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinancingExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_financing_expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('codeCenter');
            $table->string('value');
            $table->string('valueOrigin')->default(0);
            $table->string('payed')->default(0);
            $table->string('balance')->default(0);
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
        Schema::dropIfExists('cv_financing_expenses');
    }
}
