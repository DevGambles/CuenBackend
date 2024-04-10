<?php

use Illuminate\Database\Seeder;
use App\CvPotentialSubType;

class CvPotentialSubTypeTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $subTypeTask = [
            [
                'name' => 'Subir documentos',
                'order' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Validar documentos desde Administrativo',
                'order' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Validar documentos desde Juridico',
                'order' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Documentos del predio han sido aprobados',
                'order' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            /*
             * Sub tipos por si el predio potencial ha sido regresada
             */
            [
                'name' => 'Verificar documentos actualizados desde Administrativo',
                'order' => 2.5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Verificar documentos actualizados desde Juridico',
                'order' => 2.8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Subir documentos actualizados',
                'order' => 1.5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvPotentialSubType::insert($subTypeTask);
    }

}
