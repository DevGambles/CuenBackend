<?php

use Illuminate\Database\Seeder;
use App\CvRouteAutomatic;

class CvRoutesAutomaticsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $task_status = [
            [
                'role_id' => '6',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '7',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '2',
                'task_type_id' => '3',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '7',
                'task_type_id' => '3',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '3',
                'task_type_id' => '3',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '4',
                'task_type_id' => '3',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '8',
                'task_type_id' => '3',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '2',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '3',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '8',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '12',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '9',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '11',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'role_id' => '13',
                'task_type_id' => '1',
                'task_status_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
        ];

        CvRouteAutomatic::insert($task_status);
    }

}
