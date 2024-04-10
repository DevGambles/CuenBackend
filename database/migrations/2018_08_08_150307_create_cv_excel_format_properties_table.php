<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCvExcelFormatPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cv_excel_format_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cuenca')->nullable(true);
            $table->string('documento')->nullable(true);
            $table->string('propietario')->nullable(true);
            $table->string('municipio')->nullable(true);
            $table->string('vereda')->nullable(true);
            $table->string('gestion_predial')->nullable(true);
            $table->string('predio')->nullable(true);
            $table->string('psa')->nullable(true);
            $table->string('estado')->nullable(true);
            $table->string('ribera')->nullable(true);
            $table->string('nacimiento')->nullable(true);
            $table->string('bosque')->nullable(true);
            $table->string('area_bosque_estimada')->nullable(true);
            $table->string('area_adicional_conservacion_ha')->nullable(true);
            $table->string('area_intervenida')->nullable(true);
            $table->string('area_intervenida_ejecutada')->nullable(true);
            $table->string('restauracion_activa')->nullable(true);
            $table->string('restauracion_pasiva')->nullable(true);
            $table->string('area_predio_cartografica')->nullable(true);
            $table->string('area_predio_ficha')->nullable(true);
            $table->string('observaciones')->nullable(true);
            $table->string('observaciones_coord_cuenca')->nullable(true);
            $table->string('proceso')->nullable(true);
            $table->string('fuente_hidrica')->nullable(true);
            $table->string('total_acuerdo')->nullable(true);
            $table->string('activa')->nullable(true);
            $table->string('pasiva')->nullable(true);
            $table->string('mantenimiento')->nullable(true);
            $table->string('buenas_practicas')->nullable(true);
            $table->string('stard_peso')->nullable(true);
            $table->string('stard')->nullable(true);
            $table->string('mito_start')->nullable(true);
            $table->mediumText('aportante')->nullable(true);
            $table->mediumText('aportante_buenas_practicas')->nullable(true);
            $table->string('year')->nullable(true);
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
        Schema::dropIfExists('cv_excel_format_properties');
    }
}
