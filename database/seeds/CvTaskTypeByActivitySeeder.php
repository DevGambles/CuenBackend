<?php

use Illuminate\Database\Seeder;
use App\CvTaskTypeByActivity;

class CvTaskTypeByActivitySeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            /*
             * Tipo de tarea de medicion 
             */
            [
                'task_type_id' => 1,
                'activity_id' => 5
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 6
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 7
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 8
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 9
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 10
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 11
            ],
            [
                'task_type_id' => 1,
                'activity_id' => 12
            ],
            /*
             * Tipo de tarea de encuesta 
             */
            [
                'task_type_id' => 3,
                'activity_id' => 5
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 6
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 7
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 8
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 9
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 10
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 11
            ],
            [
                'task_type_id' => 3,
                'activity_id' => 12
            ],
            /*
             * Tipo de tarea de carta de intencion 
             */
            [
                'task_type_id' => 5,
                'activity_id' => 5
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 6
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 7
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 8
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 9
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 10
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 11
            ],
            [
                'task_type_id' => 5,
                'activity_id' => 12
            ]
        ];

        CvTaskTypeByActivity::insert($data);
    }

}
