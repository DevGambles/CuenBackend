<?php

use Illuminate\Database\Seeder;
use App\CvDepartament;

class CvDepartamentsTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $states = [
            ['name' => 'Amazonas'],
            ['name' => 'Antioquia'],
            ['name' => 'Arauca'],
            ['name' => 'Atlántico'],
            ['name' => 'Bogota D.C'],//5
            ['name' => 'Bolívar'],//6
            ['name' => 'Boyacá'],//7
            ['name' => 'Caldas'],//8
            ['name' => 'Caquetá'],//9
            ['name' => 'Casanare'],//10
            ['name' => 'Cauca'],//11
            ['name' => 'Cesar'],//12
            ['name' => 'Chocó'],//13
            ['name' => 'Córdoba'],//14
            ['name' => 'Cundinamarca'],//15
            ['name' => 'Guainía'],//16
            ['name' => 'Guaviare'],//17
            ['name' => 'Huila'],//18
            ['name' => 'La Guajira'],//19
            ['name' => 'Magdalena'],//20
            ['name' => 'Meta'],//21
            ['name' => 'Nariño'],//22
            ['name' => 'Norte de Santander'],//23
            ['name' => 'Putumayo'],//24
            ['name' => 'Quindío'],//25
            ['name' => 'Risaralda'],//26
            ['name' => 'San Andrés y Providencia'],//27
            ['name' => 'Santander'],//28
            ['name' => 'Sucre'],//29
            ['name' => 'Tolima'],//30
            ['name' => 'Valle del Cauca'],//31
            ['name' => 'Vaupés'],//32
            ['name' => 'Vichada']//33
        ];
        CvDepartament::insert($states);
    }

}
