<?php

use Illuminate\Database\Seeder;
use App\CvActivityCoordination;

class CvActivityCoordinationTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        /*
         * rol id  ------ name
         * 13      ------ Coordinador de comunicaciones
         * 9       ------ Apoyo de coordinación de restauracion y buenas practicas
         * 10      ------ Coordinación recurso hidrico
         * */
        $data = [
            [
                'activity_id' => 1,
                'role_id' => 13
            ],
            [
                'activity_id' => 2,
                'role_id' => 13
            ],
            [
                'activity_id' => 3,
                'role_id' => 13
            ],
            [
                'activity_id' => 4,
                'role_id' => 9
            ],
            [
                'activity_id' => 5,
                'role_id' => 9
            ],
            [
                'activity_id' => 6,
                'role_id' => 9
            ],
            [
                'activity_id' => 7,
                'role_id' => 10
            ],
            [
                'activity_id' => 8,
                'role_id' => 10
            ],
            [
                'activity_id' => 9,
                'role_id' => 10
            ],
            [
                'activity_id' => 10,
                'role_id' => 9
            ],
            [
                'activity_id' => 11,
                'role_id' => 9
            ],
            [
                'activity_id' => 12,
                'role_id' => 10
            ],
            [
                'activity_id' => 13,
                'role_id' => 9
            ],
            [
                'activity_id' => 14,
                'role_id' => 10
            ],
            [
                'activity_id' => 15,
                'role_id' => 10
            ],
            [
                'activity_id' => 16,
                'role_id' => 10
            ]
        ];
        CvActivityCoordination::insert($data);
    }

}
