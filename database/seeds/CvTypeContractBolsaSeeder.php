<?php

use App\CvTypeContractBolsa;
use Illuminate\Database\Seeder;

class CvTypeContractBolsaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type_contract = [
            [
                'name' => 'ORDEN DE COMPRA O DE SERVICOS',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'CONTRATO',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'CONVOCATORIA PÚBLICA',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'BIENES Y SERVICIOS DE PERMANENTE NECESIDAD',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvTypeContractBolsa::insert($type_contract);
    }
}
