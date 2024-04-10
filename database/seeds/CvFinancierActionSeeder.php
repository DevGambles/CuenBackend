<?php

use Illuminate\Database\Seeder;
use App\CvFinancierAction;

class CvFinancierActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Encuentros con actores - Realización de 20 actividades',//1
                'code' => '01',
                'activity_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Recurso Humano',//2
                'code' => '01',
                'activity_id' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos',//3
                'code' => '02',
                'activity_id' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Programa de Educacion Ambiental',//4
                'code' => '01',
                'activity_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',//5
                'code' => '01',
                'activity_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos',//6
                'code' => '02',
                'activity_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',//7
                'code' => '01',
                'activity_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Acciones de Restauracion',//8
                'code' => '02',
                'activity_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Indirectos',//9
                'code' => '03',
                'activity_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Acciones de Mantenimiento',//10
                'code' => '01',
                'activity_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano - Costos indirectos',//11
                'code' => '01',
                'activity_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos',//12
                'code' => '02',
                'activity_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Mantenimiento y/o rehabilitacion de STARD existentes (domesticos o industriales)',//13
                'code' => '01',
                'activity_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Implementación de sistemas de tratamiento para soluciones individuales - pozos sépticos',//14
                'code' => '02',
                'activity_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Voluntario',//15
                'code' => '01',
                'activity_id' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Programa BanCO2 / Autoridad Ambiental',//16
                'code' => '02',
                'activity_id' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Cercos Vivos',//17
                'code' => '01',
                'activity_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Arboles aislados',//18
                'code' => '02',
                'activity_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Bebederos',//19
                'code' => '03',
                'activity_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Tanques de Almacenamiento',//20
                'code' => '04',
                'activity_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Pasos de Ganado',//21
                'code' => '05',
                'activity_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Huertos Leñeros',//22
                'code' => '01',
                'activity_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Estufas Eficientes',//23
                'code' => '02',
                'activity_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Eventos',//24
                'code' => '01',
                'activity_id' => 12,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos / Contratos',//25
                'code' => '02',
                'activity_id' => 12,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',//26
                'code' => '01',
                'activity_id' => 13,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos',//27
                'code' => '02',
                'activity_id' => 13,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',//28
                'code' => '01',
                'activity_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos/Contratos',//29
                'code' => '02',
                'activity_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Transporte',//30
                'code' => '03',
                'activity_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Gastos Operativos',
                'code' => '04',
                'activity_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Equipos',
                'code' => '05',
                'activity_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',
                'code' => '01',
                'activity_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos/Contratos',
                'code' => '02',
                'activity_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Transporte',
                'code' => '03',
                'activity_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Gastos Operativos',
                'code' => '04',
                'activity_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Recurso Humano',
                'code' => '01',
                'activity_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Costos Directos',
                'code' => '02',
                'activity_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Transporte',
                'code' => '03',
                'activity_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Gastos Operativos',
                'code' => '04',
                'activity_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],

        ];
        CvFinancierAction::insert($data);
    }
}
