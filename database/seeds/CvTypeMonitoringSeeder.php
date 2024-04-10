<?php

use Illuminate\Database\Seeder;
use App\CvTypeMonitoring;

class CvTypeMonitoringSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                'name' => 'Monitoreo',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];
        CvTypeMonitoring::insert($data);
    }

}
