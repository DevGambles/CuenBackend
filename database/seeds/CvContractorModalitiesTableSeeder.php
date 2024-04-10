<?php

use Illuminate\Database\Seeder;
use App\CvContractorModality;

class CvContractorModalitiesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $modalities = [
            [
                'name' => 'Contratación directa',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Convocatoria pública número',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Invitación a cotizar',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvContractorModality::insert($modalities);
    }

}
