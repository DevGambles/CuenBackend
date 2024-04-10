<?php

use Illuminate\Database\Seeder;
use App\CvProjectActivity;

class CvProjectActivitiesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $projectActvity = [
            [
                'name' => 'Realizar encuentro con Actores',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Plan de Comunicaciones',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Crear experiencias de Educación Ambiental',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Implementación de un Sistema de Guardacuencas',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Implementación de estrategias de conservación',//Implementación estrategías de conservación en 470  hectáreas: aislamiento, enriquecimiento, establecimiento; reforestación y protección de zonas de recarga  'y descarga, protección de bosques de ladera y restauración de bosques de ribera (incluye identificación delimitación y medición de predios, estudio jurídico)
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento de acciones de conservación y protección',//Mantenimiento de las acciones de conservación y protección en las proyectadas 80 hectáreas intervenidas en 2015/2016 y 14.000 árboles
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Actividades para control de erosión puntual y/o Restauración de cauce y limpieza de quebradas',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Saneamiento básico integral',//Saneamiento básico integral: Implementación y/o mantenimiento de sistemas de tratamiento para ARD individuales - 49 sistemas
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],

            [
                'name' => 'La Fe BCO2 (50 familias) y Corantioquia (5 familias) PSA ',//Pago por Servicio Ambiental - PSA
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Ganaderia Sostenible y Buenas Practicas',//Ganadería sostenible y BPAs: cercos vivos, árboles aislados, pasos de ganado, bebederos, tanques de almacenamiento, composteras y Kits
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Prevención de la Deforestación (estufas eficientes - huertos leñeros)',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Estudios y eventos academicos',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Sistemas de Información',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Monitoreo y Seguimiento Calidad - Hidrologico',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Monitoreo Ecosistemico y Predial',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Socioeconomico',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
        ];

        CvProjectActivity::insert($projectActvity);
    }

}
