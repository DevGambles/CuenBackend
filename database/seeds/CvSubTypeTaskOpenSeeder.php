<?php

use Illuminate\Database\Seeder;
use App\CvTaskOpenSubType;

class CvSubTypeTaskOpenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subTypeTask = [
            //--- Sub tipo 1 ---//
            [
                'name' => 'Tarea Abierta',//1
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea PSA',//2
                'go_to' => 39,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Erosion',//3
                'go_to' => 26,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Hidrico',//4
                'go_to' => 21,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Comunicacion',//5
                'go_to' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Comunicacion Encuentros con actores',//6
                'go_to' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Comunicacion Plan de comunicaciones',//7
                'go_to' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Tarea Comunicacion Experiencias de educación ambiental',//8
                'go_to' => 5,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Realizar medición',//9
                'go_to' => 10,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Enviar a Revisiôn y Adiciôn',//10 //a sig
                'go_to' => 11,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Revisiôn y Adiciôn',//11
                'go_to' => 12,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => '	Entrega Parcial',//12
                'go_to' => 13,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => '	Pendiente de aprobación / Equipo de seguimiento',//13
                'go_to' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea de ejecucion por asignar',//14
                'go_to' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Pendiente por finalizar',//15
                'go_to' => 9,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea de ejecucion medicion finalizada',//16
                'go_to' => 17,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ] ,[
                'name' => 'Seguimiento en predio finalizado ',//17
                'go_to' => 17,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea a Contratista / Cargar formularios',//18
                'go_to' => 19,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea a Contratista / Cargar Documentos',//19
                'go_to' => 20,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Financiero',//20
                'go_to' => 20,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tecnico de Monitoreo Carga Muestras',//21
                'go_to' => 21,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Muestras Cargadas enviar a SIG',//22
                'go_to' => 23,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea Hidrico Editar Mapa',//23
                'go_to' => 24,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea Hidrico Mapa Editado',//24
                'go_to' => 25,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Tarea Hidrico Finalizada',//25
                'go_to' => 25,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Guarda Cuenca Carga Muestras',//26
                'go_to' => 26,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],[
                'name' => 'Muestras Cargadas enviar a SIG',//27
                'go_to' => 28,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Proceso Erosivos / Stards Editar Mapa',//28
                'go_to' => 29,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Proceso Erosivos / Stards Mapa Editado',//29
                'go_to' => 30,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Proceso Erosivos / Stards Finalizada',//30
                'go_to' => 30,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta',//31
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta psa',//32
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta erosion',//33
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta hidrico',//34
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta Comunicacion Encuentro con actores',//35
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta Comunicacion Plan de comunicaciones',//36
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta Comunicacion Experiencias de educación ambiental',//37
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'Abierta Finalizada',//38
                'go_to' => 38,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'PSA Carga de Medicion Enviar a Sig',//39
                'go_to' => 40,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'PSA Edicion Medicion Sig',//40
                'go_to' => 41,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'PSA  Medcion en Restauracion',//41
                'go_to' => 42,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
            ,[
                'name' => 'PSA Finalizada',//42
                'go_to' => 42,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];

        CvTaskOpenSubType::insert($subTypeTask);
    }
}
