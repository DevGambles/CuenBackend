<?php

use App\CvBudgetPrice;
use Illuminate\Database\Seeder;
use App\CvActions;

class CvActionsTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                'name' => 'Establecimiento (ribera)',//1
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#FFDA8C',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Establecimiento (ladera)',//2
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#8EB400',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Establecimiento (nacimiento)',//3
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#C1FFF2',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento establecimiento',//4
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#80808080',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Enriquecimiento (ribera)',//5
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#FFDA8C',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Enriquecimiento (ladera)',//6
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#8EB400',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Enriquecimiento (nacimiento)',//7
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#C1FFF2',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento enriquecimiento', //8
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#80FF0000',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (ribera)', //9
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (ladera)', //10
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (nacimiento)', //11
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#800000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (establecimento)', //12
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#800000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (enriquecimiento)', //13
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#800000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre pua (ribera)', //14
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre pua (ladera)', //15
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre pua (nacimiento)', //16
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#C500FF',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (ribera)', //17
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (ladera)', //18
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (nacimiento)', //19
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#C500FF',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (establecimento)', //20
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#808000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (enriquecimiento)', //21
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#808000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre liso (ribera)', //22
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre liso (ladera)', //23
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con plantulas con alambre liso (nacimiento)', //24
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#C500FF',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Broche aislamiento alambre pua', //25
                'type' => 'punto',
                'color' => '#008000',
                'good_practicess' => 0,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Broche aislamiento alambre liso', //26
                'type' => 'punto',
                'color' => '#00FFFF',
                'good_practicess' => 0,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento aislamiento con plantulas', //27
                'type' => 'accion',
                'color' => '#008080',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cerco vivo aislamiento liso un lado', //28
                'type' => 'accion',
                'color' => '#DAF7A6',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cerco vivo aislamiento liso ambos lados', //29
                'type' => 'accion',
                'color' => '#0000FF',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cerco vivo aislamiento pua un lado', //30
                'type' => 'accion',
                'color' => '#000080',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Arboles dispersos con aislamiento (1,20 cm)', //31
                'type' => 'punto',
                'color' => '#FF00FF',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Arboles dispersos con aislamiento (80cm)', //32
                'type' => 'punto',
                'color' => '#800080',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Arboles dispersos con aislamiento (1,20 cm)', //33
                'type' => 'punto',
                'color' => '#C39BD3',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Arboles dispersos con aislamiento (80cm)', //34
                'type' => 'punto',
                'color' => '#FF5733',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Estufa eficiente', //35
                'type' => 'punto',
                'color' => '#E9967A',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Huerto leñero', //36
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Tanque de almacenamiento 2000 litros', //37
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Suministro de bebederos', //38
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Paso de ganado madera inmunizada', //39
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Sistema de tratamiento aguas residuales', //40
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Mantenimiento sistema de tratamiento aguas residuales', //41
                'type' => 'punto',
                'color' => '#F1C40F',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cerco vivo aislamiento pua ambos lados', //42
                'type' => 'accion',
                'color' => '#000080',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Cerco vivo sin cerco', //43
                'type' => 'accion',
                'color' => '#000080',
                'good_practicess' => 1,
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]//
            ,
            [
                'name' => 'Enriquecimiento con alambre liso (ladera)', //44
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Enriquecimiento con alambre liso (ribera)', //45
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Enriquecimiento con alambre liso (nacimiento)', //46
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#C500FF',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento (ladera)', //47
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#8EB400',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento (ribera)', //48
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#FFDA8C',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento (nacimiento)', //49
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#C1FFF2',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Establecimiento (ribera)', //50
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Establecimiento (ladera)', //51
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A9F180',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Establecimiento (nacimiento)', //52
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#C500FF',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (establecimento) ribera', //53
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (establecimento) ladera', //54
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FF0000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (establecimento) nacimiento', //55
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A900E6',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (enriquecimiento) ribera', //56
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (enriquecimiento) ladera', //57
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FF0000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con pua (enriquecimiento) nacimiento', //58
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A900E6',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (establecimento) ribera', //59
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (establecimento) ladera', //60
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FF0000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (establecimento) nacimiento', //61
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A900E6',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (enriquecimiento) ribera', //62
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FFFF00',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (enriquecimiento) ladera', //63
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#FF0000',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Aislamiento con alambre liso (enriquecimiento) nacimiento', //64
                'type' => 'accion',
                'good_practicess' => 0,
                'color' => '#A900E6',
                'color_fill' => '',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            //todo No ACTIVITIES
            [
                'name' => 'Area de Conservación Adicional', //65
                'type' => 'area',
                'good_practicess' => 0,
                'color' => '',
                'color_fill' => '#FFFFFF',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];
        CvActions::insert($data);
    }

}
