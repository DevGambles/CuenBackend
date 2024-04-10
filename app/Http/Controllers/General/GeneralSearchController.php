<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\CvProperty;
use App\CvTask;
use App\CvProcess;
use App\CvMonitoring;
use App\Http\Controllers\General\GeneralMonitoringController;

class GeneralSearchController extends Controller {

    //*** Actualizar información del mapa en algolia cuando se actualice la encuesta ***//

    /*
     *  Obtener el id del GeoJsonRegistrado en el mapa
     */

    public function updateSearchMapByProperty($property_id) {

        $property = CvProperty::find($property_id);

        $property->taskOne;

        if (!empty($property)) {

            if (!empty($property->taskOne->id)) {

                $taskProcess = CvTask::find($property->taskOne->id)->process[0];

                //--- Obtener procedimiento de la encuesta para obtener informacion del mapa ---//

                $processTasks = CvProcess::find($taskProcess->id)->processByTasks;

                foreach ($processTasks as $taskMap) {

                    //--- Tipo de tarea mapa ---//
                    if ($taskMap->task_type_id == 1) {

                        //--- Validar que la tarea cuente con un mapa ---//

                        $taskMapGeoJson = CvTask::find($taskMap->id);

                        if (isset($taskMapGeoJson->geoJsonOne->id)) {
                            return $taskMapGeoJson->geoJsonOne->id;
                        } else {
                            return 0;
                        }
                    }
                }
            }
        }
    }

    //*** Actualizar información del monitoreo cuando se actualice la encuesta y el mapa ***//

    /*
     *  Obtener el id del monitoreo
     * 
     *  1. Se envia como parametro el ID de la tarea
     */

    public function updateSearchMonitoringByTask($task_id) {

        //--- Obtener procedimiento ---//
        $process = CvTask::find($task_id);

        if (!empty($process)) {
            $idProcess = $process->process[0]->id;

            //--- Consultar los monitoreos ---//

            $monitorings = CvMonitoring::where("process_id", $idProcess)->get();

            foreach ($monitorings as $monitoring) {

                //--- Actualizar cada monitoreo de una tarea ---//

                $instanceMonitoring = new GeneralMonitoringController();

                $instanceMonitoring->infoSearchMonitoring($monitoring->id);
            }
        }
    }

}
