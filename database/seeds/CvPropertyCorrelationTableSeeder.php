<?php

use Illuminate\Database\Seeder;
use App\CvPropertyCorrelation;

class CvPropertyCorrelationTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $property_correlation = [
                [
                'name' => 'Propietario',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'Poseedor',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'Representante legal',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'Arrendatario',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'Tenedor',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,
            [
                'name' => 'Sin Asignación',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvPropertyCorrelation::insert($property_correlation);
    }

}
