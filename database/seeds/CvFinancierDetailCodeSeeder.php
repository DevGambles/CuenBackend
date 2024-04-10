<?php

use Illuminate\Database\Seeder;
use App\CvFinancierDetailCode;

class CvFinancierDetailCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Transporte Profesionales en transporte Publico (Ida y regreso) ',
                'code' => 'AAA01001',
                'actiion_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Refrigerio  (Aprox. 23 Personas por Taller o reunion) 20 reuniones talleres',
                'code' => 'AAA01002',
                'actiion_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Alquiler lugares de reunion ',
                'code' => 'AAA01003',
                'actiion_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Alquiler Equipos para ferias o eventos (stand, telepronter, video bold, entre otros) ',
                'code' => 'AAA01004',
                'actiion_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Comunicador-adriana',
                'code' => 'ABA01001',
                'actiion_id' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Trabajador Social - Lorena',
                'code' => 'ABA01002',
                'actiion_id' => 2,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Diseño, ilustración e impresión plegables y/o brochure para difusion',
                'code' => 'ABA02001',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Video institucional o mobiliario para divulgacion (video influenciadores, video trabajo educación ambiental)',
                'code' => 'ABA02002',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Cuñas radiales, televisivas y notas periodísticas',
                'code' => 'ABA02003',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Campaña ambiental para posicionar CuencaVerde  ',
                'code' => 'ABA02004',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Aparición en ferias ambientales y eventos ecológicos ',
                'code' => 'ABA02005',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Souvenirs  y/o material de divulgación para participación en eventos, talleres, reuniones con empresarios ',
                'code' => 'ABA02006',
                'actiion_id' => 3,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Diseño, ilustración de guías de Educación Ambiental',
                'code' => 'ABB01001',
                'actiion_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Impresión de guias de educación ambiental',
                'code' => 'ABB01002',
                'actiion_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Siembras experienciales de 1500 árboles nativos (3 siembras) ',
                'code' => 'ABB01003',
                'actiion_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Guardacuenca - Técnico',
                'code' => 'ABC01001',
                'actiion_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Guardacuencas Apoyo a Mantenimiento',
                'code' => 'ABC01002',
                'actiion_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Coordinador Guardacuenca Profesional',
                'code' => 'ABC01003',
                'actiion_id' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Transporte',
                'code' => 'ABC02001',
                'actiion_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Dotacion de identificación profesionales (camisa polo, gorra, carnet institucional)',
                'code' => 'ABC02002',
                'actiion_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Equipos',
                'code' => 'ABC02003',
                'actiion_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Reuniones Guardacuencas',
                'code' => 'ABC02004',
                'actiion_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Capacitaciones',
                'code' => 'ABC02005',
                'actiion_id' => 6,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Coordinador',
                'code' => '001',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional de Apoyo a Coordinación',
                'code' => 'BAA01002',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional SIG',
                'code' => 'BAA01003',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Personal Administrativo',
                'code' => 'BAA01004',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional Equipo Supervisión',
                'code' => 'BAA01005',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional Equipo Supervisión',
                'code' => 'BAA01006',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional Equipo Supervisión conocimiento en SIG',
                'code' => 'BAA01007',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Tecnólogo técnico Equipo Supervisión',
                'code' => 'BAA01008',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Jornales de campo',
                'code' => 'BAA01009',
                'actiion_id' => 7,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Transporte',
                'code' => 'BAA03001',
                'actiion_id' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Imprevistos predios y contratos',
                'code' => 'BAA03002',
                'actiion_id' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Gastos operativos (caja menor)',
                'code' => 'BAA03003',
                'actiion_id' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Coodinación',
                'code' => 'BBA01001',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Profesional del Supervisión',
                'code' => 'BBA01002',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Personal administrativo',
                'code' => 'BBA01003',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Interventoria',
                'code' => 'BBA01004',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Transporte',
                'code' => 'BBA01005',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],  [
                'name' => 'Imprevistos/obra adicional',
                'code' => 'BBA01006',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => 'Gastos Operativos',
                'code' => 'BBA01007',
                'actiion_id' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ], [
                'name' => '001- Costos Directos/Contratos',
                'code' => 'BBA02001',
                'actiion_id' => 12,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Costos Directos/Contratos (mantenimiento)',
                'code' => 'BBB01001',
                'actiion_id' => 13,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Instalación STARD/Contratos',
                'code' => 'BBB02001',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Coodinación',
                'code' => 'BBB02002',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Profesional del Supervisión',
                'code' => 'BBB02003',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Personal administrativo',
                'code' => 'BBB02004',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Interventoria',
                'code' => 'BBB02005',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Transporte',
                'code' => 'BBB02006',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Imprevistos/ obra adicional',
                'code' => 'BBB02007',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Gastos Operativos',
                'code' => 'BBB02008',
                'actiion_id' => 14,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],

            [
                'name' => 'Coordinador',
                'code' => 'BAA02001',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Profesional de Apoyo a Coordinación',
                'code' => 'BAA02002',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Profesional SIG',
                'code' => 'BAA02003',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Personal Administrativo',
                'code' => 'BAA02004',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Profesional Equipo Supervisión',
                'code' => 'BAA02005',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Profesional Equipo Supervisión conocimiento en SIG',
                'code' => 'BAA02006',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Tecnólogo tècnico Equipo Supervisión',
                'code' => 'BAA02007',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Jornales de campo',
                'code' => 'BAA02008',
                'actiion_id' => 8,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],

            //SIN DETALLE
            [
                'name' => 'Acciones de Mantenimiento',
                'code' => 'BAB01001',
                'actiion_id' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Voluntario',
                'code' => 'BBC01001',
                'actiion_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Programa BanCO2 / Autoridad Ambiental',
                'code' => 'BBC02001',
                'actiion_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cercos Vivos',
                'code' => 'BCA01001',
                'actiion_id' => 17,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Arboles aislados',
                'code' => 'BCA02001',
                'actiion_id' => 18,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Bebederos',
                'code' => 'BCA03001',
                'actiion_id' => 19,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Tanques de Almacenamiento',
                'code' => 'BCA04001',
                'actiion_id' => 20,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Pasos de Ganado',
                'code' => 'BCA05001',
                'actiion_id' => 21,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Huertos Leñeros',
                'code' => 'BCB01001',
                'actiion_id' => 22,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Estufas Eficientes',
                'code' => 'BCB01002',
                'actiion_id' => 23,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Eventos',
                'code' => 'CAA01001',
                'actiion_id' => 24,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],

            [
                'name' => 'Costos Directos / Contratos',
                'code' => 'CAA02001',
                'actiion_id' => 25,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Recurso Humano',
                'code' => 'CAB01001',
                'actiion_id' => 26,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Costos Directos',
                'code' => 'CAB02001',
                'actiion_id' => 27,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //Sin detalle en codigo
            [
                'name' => 'Recurso Humano',
                'code' => 'DAA01',
                'actiion_id' => 28,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Costos Directos/Contratos',
                'code' => 'DAA02',
                'actiion_id' => 29,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Transporte',
                'code' => 'DAA03',
                'actiion_id' => 30,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Gastos Operativos',
                'code' => 'DAA04',
                'actiion_id' => 31,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Equipos',
                'code' => 'DAA05',
                'actiion_id' => 32,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Recurso Humano',
                'code' => 'DAB01',
                'actiion_id' => 33,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Costos Directos/Contratos',
                'code' => 'DAB02',
                'actiion_id' => 34,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Transporte',
                'code' => 'DAB03',
                'actiion_id' => 35,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Gastos Operativos',
                'code' => 'DAB04',
                'actiion_id' => 36,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Recurso Humano',
                'code' => 'DAC01',
                'actiion_id' => 37,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Costos Directos',
                'code' => 'DAC02',
                'actiion_id' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Transporte',
                'code' => 'DAC03',
                'actiion_id' => 39,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Gastos Operativos',
                'code' => 'DAC04',
                'actiion_id' => 40,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]

        ];
        CvFinancierDetailCode::insert($data);
    }
}
