<?php

use Illuminate\Database\Seeder;
use App\CvSubTypeTask;

class CvSubTypeTaskSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $subTypeTask = [
            //--- Sub tipo 1 ---//
            [
                'name' => 'Llenar encuesta',
                'order' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 2 ---//
            [
                'name' => 'Revisar encuesta',
                'order' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 3 ---//
            [
                'name' => 'Visualizar encuesta',
                'order' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 4 ---//
            [
                'name' => 'Medir predio',
                'order' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 5 ---//
            [
                'name' => 'Edición de medición',
                'order' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 6 ---//
            [
                'name' => 'Aprobar en validación',
                'order' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 7 ---//
            [
                'name' => 'Edición de validación',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 8 ---// 
            [
                'name' => 'Carga de certificado de tradición',
                'order' => 2.2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 9 ---//
            [
                'name' => 'Validar certificado de tradición',
                'order' => 2.6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 10 ---//
            [
                'name' => 'Edición de validación con certificado de tradición',
                'order' => 2.8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 11 ---//
            [
                'name' => 'Solicitud de edición de mapa en verificación',
                'order' => 5.4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 12 ---//
            [
                'name' => 'Edicion SIG presupuesto, buenas prácticas',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 13 ---//
            [
                'name' => 'Aprobar validación con actualización de sig',
                'order' => 5.8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 14 ---//
            [
                'name' => 'Generación minuta coordinación guardacuencas',
                'order' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 15 ---//
            [
                'name' => 'Cargar mapa de verificación',
                'order' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 16 ---//
            [
                'name' => 'Visualizacion minuta Direccion',
                'order' => 17,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 17 ---//
            [
                'name' => 'Aprobación de presupuesto desde financiero',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 18 ---//
            [
                'name' => 'Presupuesto rechazado desde financiero',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 19 ---//
            [
                'name' => 'Aprobación de presupuesto en financiero',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 20 ---//
            [
                'name' => 'Validacion minuta administrativo',
                'order' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 21 ---//
            [
                'name' => 'Firma minuta propietario coordinador',
                'order' => 18,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 22 ---//
            [
                'name' => 'Validacion minuta juridico',
                'order' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 23 ---//
            [
                'name' => 'Firma minuta propietario guarda cuenca o validacion',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 24 ---//
            [
                'name' => 'Aprobación de dirección en presupuesto',
                'order' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 25 ---//
            [
                'name' => 'Aprobación de dirección presupuesto',
                'order' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 26 ---//
            [
                'name' => 'Aprobación de financiero presupuesto',
                'order' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 27 ---//
            [
                'name' => 'Concepto de coordinación presupuesto',
                'order' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 28 ---//
            [
                'name' => 'Concepto de jurídico presupuesto',
                'order' => 12,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 29 ---//
            [
                'name' => 'Edicion SIG presupuesto, buenas prácticas',
                'order' => 13,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 30 ---//
            [
                'name' => 'Generación de minuta coordinación precontractual',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 31 ---//
            [
                'name' => 'Validacion minuta firmada por direccion y propietario coordinador',
                'order' => 0,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 32 ---//
            [
                'name' => 'Cargar minuta firmada por propietario',
                'order' => 19,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //--- Sub tipo 33 ---//
            [
                'name' => 'Minuta firmada por dirección y presupuesto',
                'order' => 33,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvSubTypeTask::insert($subTypeTask);
    }

}
