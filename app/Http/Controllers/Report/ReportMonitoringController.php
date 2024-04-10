<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvMonitoring;
use App\CvProcess;
use App\CvTask;
use App\CvActions;
use App\CvBudgetActionMaterial;
use App\CvTaskProcess;
use App\CvProperty;
use App\CvFormMonitoring;
use App\CvProject;

class ReportMonitoringController extends Controller {

    //*** Consulta especifica para el anexo 3 del formulario STARD ***//

    public function consultFormStardMonitoring($id) {

        $informationProperty = $this->informationSpecificProperty($id);

        if (isset($informationProperty["property"]) && isset($informationProperty["geojson"])) {

            //--- Objeto que va a guardar la información de las coordenadas ---//
            $coordinates = array();

            $property = json_decode($informationProperty["property"]->info_json_general, TRUE);

            $geojson = (!empty($informationProperty["geojson"])) ? json_decode($informationProperty["geojson"][0]->geojson, true) : "";

            foreach ($geojson["features"] as $infogeojson) {

                if ($infogeojson["properties"]["hash"] == $informationProperty["hash_monitoring"]) {

                    //--- Validar si el hash es de un punto o STARD ---//

                    $actionMaterial = CvBudgetActionMaterial::where("action_id", $infogeojson["properties"]["AccionId"])->first()->budgetPriceMaterial;

                    if ($actionMaterial->type == "STARD") {

                        $coordinates["lat"] = $infogeojson["geometry"]["coordinates"][0];
                        $coordinates["lon"] = $infogeojson["geometry"]["coordinates"][1];
                    } else {
                        return [
                            "message" => "El monitoreo no cuenta con una relación de tipo STARD en el mapa seleccionado",
                            "response_code" => 200
                        ];
                    }
                }
            }

            //--- Guardar toda la información resumida ---//

            $info = array();

            array_push($info, array(
                "id" => $informationProperty["monitoring_id"],
                "property_name" => $property["property_name"],
                "municipality" => $property["municipality"],
                "lane" => $property["lane"],
                "contact" => $property["contact"],
                "coordinate" => $coordinates
            ));

            return (!empty($info)) ? $info[0] : [];
        } else {

            return $informationProperty;
        }
    }

    //*** Informacion especifica de la encuesta ***//

    public function informationSpecificProperty($id) {

        $info = array();

        //--- Consultar monitoreo para obtener su procedimiento ---//
        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Consultar el procedimiento ---//
        $process = CvProcess::find($monitoring->process_id);

        if (count($process->processByTasks) == 0) {
            return [
                "message" => "El procedimiento no cuenta con tareas asignadas",
                "response_code" => 200
            ];
        }

        //--- Consultar si se encuentra la tarea de encuesta por el procedimiento y de mapa ---//

        $property = 0;
        $geojson = 0;

        foreach ($process->processByTasks as $task) {

            if ($task->task_type_id == 1 || $task->task_type_id == 3) {
                $geojson = 1;
                $property = 1;
            }
        }

        //--- Las variables indican que si se encontraron las tareas de encuesta y medicion ---//

        if ($property == 1 && $geojson == 1) {

            foreach ($process->processByTasks as $task) {

                $info["monitoring_id"] = $monitoring->id;
                $info["property"] = CvTask::find($task->id)->property;
                $info["geojson"] = (!empty(CvTask::find($task->id)->geoJson)) ? CvTask::find($task->id)->geoJson : "";
                $info["hash_monitoring"] = $monitoring->hash_map;

                //--- Validar que el geojson es el mismo del monitoreo y es de tipo STARD ---//
                //return json_decode($task->geoJson[0]["geojson"], true);

                return $info;
            }
        } else {
            return [
                "message" => "El procedimiento no cuenta aun con mapa o encuesta",
                "response_code" => 200
            ];
        }
    }

    //Anexo 9
    public function consultFormTrackingPredial($id) {
        $info = array();
        $activities = array();
        $encuesta = "";
        //--- Consultar monitoreo para obtener su procedimiento ---//
        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        $process = CvProcess::find($monitoring->process_id);
        if (empty($process)) {
            return [
                "message" => "El proceso no existe para el monitoreo",
                "response_code" => 200
            ];
        }
        $taskbyprocess = CvTaskProcess::where('process_id', $process->id)->get();

        foreach ($taskbyprocess as $taskRecord) {
            $encuesta = CvTask::where('id', $taskRecord->task_id)->where('task_type_id', 3);
            if ($encuesta->exists()) {
                $encuesta = $encuesta->first();
            }
        }

        if (empty($encuesta)) {
            return [
                "message" => "Tarea con encuesta no encontrada",
                "response_code" => 200
            ];
        }

        $property = CvProperty::where('id', $encuesta->property_id)->first();
        $propertyInfo = json_decode($property->info_json_general, true);

        foreach ($process->processByProjectByActivity->pluck('name') as $value) {
            array_push($activities, array(
                "activity" => $value
            ));
        }
        $project_id = 0;

        for ($i = 0; $i < count($process->processByProjectByActivity->pluck('project_id')); $i++) {
            if ($process->processByProjectByActivity->pluck('project_id')[0] == $process->processByProjectByActivity->pluck('project_id')[$i]) {
                $project_id = $process->processByProjectByActivity->pluck('project_id')[0];
            } else {
                $project_id = 0;
                return [
                    "message" => "El id del proyecto en los procesos es diferente",
                    "response_code" => 200
                ];
                break;
            }
        }

        if ($project_id == 0) {
            return [
                "message" => "No se encuentra id de proyecto",
                "response_code" => 200
            ];
        }

        $projectname = CvProject::find($project_id);
        //OBJETO PENDIENTE ANEXO 9
        array_push($info, array(
            "micro_basin" => $propertyInfo["micro_basin"],
            "property_name" => $propertyInfo["property_name"],
            "date" => $propertyInfo["property_visit_date"]["day"] . "/" . $propertyInfo["property_visit_date"]["month"] . "/" . $propertyInfo["property_visit_date"]["year"],
            "lane" => $propertyInfo["lane"],
            "municipality" => $propertyInfo["municipality"],
            "project" => $projectname->name,
            "activities" => $activities
        ));

        return $info;
    }

    //Anexo 10
    public function consultFormVegetalMonitoring($id) {
        $info = array();
        $encuesta = "";
        //--- Consultar monitoreo para obtener su procedimiento ---//
        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        $process = CvProcess::find($monitoring->process_id);
        if (empty($process)) {
            return [
                "message" => "El proceso no existe para el monitoreo",
                "response_code" => 200
            ];
        }
        $taskbyprocess = CvTaskProcess::where('process_id', $process->id)->get();

        foreach ($taskbyprocess as $taskRecord) {
            $encuesta = CvTask::where('id', $taskRecord->task_id)->where('task_type_id', 3);
            if ($encuesta->exists()) {
                $encuesta = $encuesta->first();
            }
        }

        if (empty($encuesta)) {
            return [
                "message" => "Tarea con encuesta no encontrada",
                "response_code" => 200
            ];
        }

        $property = CvProperty::where('id', $encuesta->property_id)->first();
        $propertyInfo = json_decode($property->info_json_general, true);
        $formonitoring = CvFormMonitoring:: where('monitoring_id', $id)->first();
        if (empty($formonitoring)) {
            return [
                "message" => "Formulario de monitoreo no encontrado",
                "response_code" => 200
            ];
        }

        //FORMULARIO DE MONITOREO VEGETAL captura de json en formonitoring DATOS
        array_push($info, array(
            "contact" => $propertyInfo["contact"]["contact_name"],
            "property_name" => $propertyInfo["property_name"],
            "lane" => $propertyInfo["lane"],
            "municipality" => $propertyInfo["municipality"],
            "contractor_name" => $monitoring->userByMonitoring->name
        ));
        return $info;
    }
    //Anexo 13
    public function consultFormSupplierEvaluation($id) {
        return "anexo 13";
    }

}
