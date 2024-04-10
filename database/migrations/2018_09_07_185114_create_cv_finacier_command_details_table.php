<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvFinacierCommandDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_finacier_command_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('inversion')->nullable();
            $table->string('inversion_species')->nullable();
            $table->mediumText('paid')->nullable(true);
            $table->mediumText('committed')->nullable(true);
            $table->mediumText('committed_balance')->nullable(true);
            $table->mediumText('balance')->nullable(true);
            $table->integer('year');
            $table->integer('type');
            $table->string('code')->nullable(true);
            $table->integer('associated_id')->unsigned();
            $table->foreign('associated_id')->references('id')->on('cv_associateds');
            $table->integer('financier_detail_id')->unsigned();
            $table->foreign('financier_detail_id')->references('id')->on('cv_financier_detail_codes');
            $table->integer('contributions_id')->unsigned();
            $table->foreign('contributions_id')->references('id')->on('cv_associated_contributions');
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
        Schema::dropIfExists('cv_finacier_command_details');
    }
}
