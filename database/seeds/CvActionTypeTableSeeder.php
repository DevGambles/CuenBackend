<?php

use Illuminate\Database\Seeder;
use App\CvActionType;

class CvActionTypeTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $actionActvity = [
            [
                'name' => 'Mantenimiento'//1
            ],
            [
                'name' => 'Liso'//2
            ],
            [
                'name' => 'Pua'//3
            ],
            [
                'name' => 'Aislamiento'//4
            ],
            [
                'name' => 'Ribera'//5
            ],
            [
                'name' => 'Ladera'//6
            ],
            [
                'name' => 'Nacimiento'//7
            ],
            [
                'name' => 'Establecimento'//8
            ],
            [
                'name' => 'Enriquecimiento'//9
            ],
            [
                'name' => 'Buenas Practicas'//10
            ],
        ];

        CvActionType::insert($actionActvity);
    }

}
