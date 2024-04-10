<?php

use Illuminate\Database\Seeder;
use App\CvTypeContract;

class CvTypeContractsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $typeContract = [
            [
                'name' => 'Prestación de servicios',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Orden de servicios',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Orden de compra',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
              ,
            [
                'name' => 'Contrato de Transporte',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
              
        ];

        CvTypeContract::insert($typeContract);
    }
}
