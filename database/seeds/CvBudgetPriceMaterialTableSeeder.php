<?php

use Illuminate\Database\Seeder;
use App\CvBudgetPriceMaterial;

class CvBudgetPriceMaterialTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            //*** Tipo restauracion ***//

            [
                'name' => 'Presupuesto restauración por hectárea (1667 ind/ha)',
                'price' => '5038113',
                'type' => 'restauracion',
                'measurement' => '10000',
                'unit_id' => '3',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto actividades de mantenimiento para restauracion ecologica activa de pastizales (por hectárea) (4 mantenimientos)',
                'price' => '3455969',
                'type' => 'restauracion',
                'measurement' => '10000',
                'unit_id' => '3',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto enriquecimiento y restauración de zonas de recarga para cada acción (500 ind/ha)',
                'price' => '1666373',
                'type' => 'restauracion',
                'measurement' => '10000',
                'unit_id' => '3',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto actividades de mantenimiento para enriquecimiento (500 árboles) y restauración de zonas de recarga (4 mantenimientos)',
                'price' => '1036625',
                'type' => 'restauracion',
                'measurement' => '10000',
                'unit_id' => '3',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //*** Tipo alambre ***//
            [
                'name' => 'Presupuesto aislamiento metro con pua poste inmunizado',
                'price' => '10319',
                'type' => 'alambre',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto aislamiento metro con pua poste inmunizado con plántula',
                'price' => '11830',
                'type' => 'alambre',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto aislamiento metro con alambre poste liso',
                'price' => '6034',
                'type' => 'alambre',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto aislamiento metro con alambre poste liso con plántula',
                'price' => '6962',
                'type' => 'alambre',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //*** Tipo de unidad ***//
            [
                'name' => 'Broches púa',
                'price' => '37321',
                'type' => 'broches',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Broches liso',
                'price' => '30467',
                'type' => 'broches',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //*** Tipo cerca ***//
            [
                'name' => 'Presupuesto mantenimiento aislamiento liso con plántula',
                'price' => '1042',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto cercas vivas por 1000 ml (500 arboles) protegido con cerco eléctrico por un solo lado',
                'price' => '4761',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto cercas vivas por 1000 ml (500 arboles) protegido con cerco eléctrico por ambos lados',
                'price' => '8006',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto cercas vivas por 1000 ml (500 arboles) protegido con cerco de púa por un solo lado',
                'price' => '8165',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto cercas vivas por 1000 ml (500 arboles) protegido con cerco de púa por ambos lados',
                'price' => '11412',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto cercas vivas 1000 ml (500 árboles) sin cerco',
                'price' => '1511',
                'type' => 'cerca viva',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Presupuesto huerto leñero 500 m2 (222 árboles)',
                'price' => '698384',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Estufa eficiente con instalación',
                'price' => '1250000',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Árboles dispersos con aislamiento',
                'price' => '122171',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Árboles dispersos con aislamiento (árboles nativos de altura primedio 80 cm)',
                'price' => '106671',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Árboles dispersos sin aislamiento',
                'price' => '24175',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Árboles dispersos sin aislamiento (árboles nativos de altura promedio 80 cm)',
                'price' => '8675',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Tanque de almacenamiento de 2000 litros',
                'price' => '531600',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Suministro de bebederos ',
                'price' => '288063',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Paso de ganado con madera inmunizada',
                'price' => '3477460',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Sistema de tratamiento de aguas residuales',
                'price' => '4895979',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento sistema de tratamiento de aguas residuales',
                'price' => '1200000',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento Huerto Leñero',
                'price' => '114849',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Compostera EARTH GREEN SAC 500',
                'price' => '785000',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
             [
                'name' => 'Siembra x arbol disperso con aislamiento',
                'price' => '112457',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
             [
                'name' => 'Siembra x árbol disperso sin aislamiento',
                'price' => '19137',
                'type' => 'STARD',
                'measurement' => '1',
                'unit_id' => '4',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [//32
                'name' => 'Presupuesto enriquecimiento metro con alambre poste liso',
                'price' => '0',
                'type' => 'restauracion',
                'measurement' => '1',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [//34
                'name' => 'N/A',
                'price' => '0',
                'type' => 'restauracion',
                'measurement' => '2',
                'unit_id' => '1',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvBudgetPriceMaterial::insert($data);
    }

}
