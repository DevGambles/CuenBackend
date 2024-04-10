<?php

use Illuminate\Database\Seeder;
use App\CvTaskStatus;

class CvTaskStatusTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $task_status = [
                [
                'name' => 'Asignada',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'En proceso',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
                [
                'name' => 'Atrasada',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvTaskStatus::insert($task_status);
    }

}
