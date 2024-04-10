<?php

use Illuminate\Database\Seeder;
use App\CvTypePqrs;

class CvTypePqrsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                'name' => 'Reclamo',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Solicitud',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];
        CvTypePqrs::insert($data);
    }

}
