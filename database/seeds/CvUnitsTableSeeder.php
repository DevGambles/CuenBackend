<?php

use Illuminate\Database\Seeder;
use App\CvUnits;

class CvUnitsTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                'name' => 'Metro lineal',
                'symbol' => 'ml',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Metro cuadrado',
                'symbol' => 'm2',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Hectárea',
                'symbol' => 'ha',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Unidad',
                'symbol' => 'und',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];
        CvUnits::insert($data);
    }

}
