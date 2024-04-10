<?php

namespace App\Http\Controllers\General;

use App\CvAllUserTasksMeasurement;
use App\CvBudgetPriceMaterial;
use App\CvMaintenanceForAction;
use App\CvTaskUser;
use function GuzzleHttp\Promise\task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvGeoJson;
use App\CvTask;
use App\Http\Controllers\General\GeneralSubTypeTaskController;
use App\CvBudgetActionMaterial;
use App\CvBudget;
use App\CvProcess;
use App\CvProperty;
use App\User;
use ArrayObject;
use App\CvGeoJsonUser;
use App\Http\Controllers\Search\SearchAlgoliaController;
use App\Http\Controllers\General\GeneralSearchController;
use Illuminate\Support\Facades\Storage;
use App\CvAdminPorcentBudget;

class GeneralMapController extends Controller {

    //*** Obtener las coordenadas del recorrido de un predio por tarea ***//

    public function propertyGeoJsonTask($request) {


        //*** Permiso que se le va a indicar al mapa segun el rol que lo este modificando ***//

        $permissionRoute = "permission_geo_json";

        //*** Consultar tarea para funciones como: ***//
        //--- 1. Cambiar su estado ---//
        //--- 2. Filtrar las rutas para determinar el envio automatico al rol de Sig ---//

        $updateTask = CvTask::find($request->task_id);

        if (empty($updateTask)) {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200,
            ];
        }

        //--- Validar que la tarea sea de medicion ---//
        if ($updateTask->task_type_id != 1) {
            return [
                "message" => "La tarea solicitada no es de tipo medicion",
                "response_code" => 200,
            ];
        }

        //--- Ingresar el tipo de tarea de acuerdo al usuario ---//

        $subTypeController = new GeneralSubTypeTaskController();
        $updateTask->task_sub_type_id = $subTypeController->subTypeTask($this->userLoggedInRol(), $updateTask->task_type_id, $updateTask->task_sub_type_id);

        //*** Guardar cambios de tarea ***//

        $updateTask->save();

        //--- Asignarle la tarea a rol de sig ---//

        $info = array(
            //--- Información para el filtro de las rutas automaticas ---//
            "permission_route" => $permissionRoute,
            "task_id" => $updateTask->id,
            "task_type_id" => $updateTask->task_type_id,
            "task_status_id" => $updateTask->task_status_id,
        );

        //--- Funcion para filtrar los roles que se van a encargar de la tarea enviada ---//
        $filterSendTask = $this->routesAutomatics($info);

        // --- Instancia de la clase del controlador de busqueda --- //
        $searchController = new GeneralSearchController();

        //--- Guardar el historial de la tarea ---//
        if (isset($filterSendTask["user"]) && isset($filterSendTask["task"])) {

            //--- Enviar la notificacion ---//

            $notificationTask = new GeneralNotificationController();

            $content = "El guarda cuenca " . User::find($filterSendTask["user"])->name . " ha realizado la medición del "
                . "procedimiento " . $updateTask->process[0]->name . ".";

            $notificationTask->notificationTask($updateTask, $content, $filterSendTask["user"]);

            // --- Enviar información de la tarea para registrar su historial ---//
            $historyTask = array();

            array_push($historyTask, array(
                    "type_task" => "GeoJson_task",
                    "info" => $request->geojson,
                    "task_id" => $filterSendTask["task"],
                    "user_from" => $this->userLoggedInId(),
                    "user_to" => $filterSendTask["user"]
                )
            );

            // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
            $historyController = new GeneralHistoryTaskController();

            if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                //*** Consultar si la tarea ya tiene un mapa registrado ***//

                $CvGeoJsonExist = CvGeoJson::where("task_id", $request->task_id)->count();

                if ($CvGeoJsonExist == 1) {

                    $updateCvGeoJson = CvGeoJson::where("task_id", $request->task_id)->first();

                    $updateCvGeoJson->geojson = $request->geojson;

                    if ($updateCvGeoJson->save()) {

                        // --- Guardar informacion del buscador --- //

                        $this->infoSearchGeoJson($updateCvGeoJson->id);

                        // --- Actualizar información del monitoreo para el buscador enviando el Id de la tarea --- //

                        $searchController->updateSearchMonitoringByTask($updateTask->id);

                        return [
                            "message" => "Registro actualizado",
                            "response_code" => 200
                        ];
                    }
                } else {

                    //*** Registrar mapa a la tarea asignada ***//

                    $newCvGeoJson = new CvGeoJson();

                    $newCvGeoJson->geojson = $request->geojson;
                    $newCvGeoJson->task_id = $request->task_id;

                    if ($newCvGeoJson->save()) {

                        // --- Guardar informacion del buscador --- //
                        $this->infoSearchGeoJson($newCvGeoJson->id);

                        return [
                            "message" => "Registro exitoso",
                            "response_code" => 200
                        ];
                    }
                }
            }
        }

        //*** Mostrar mensaje si la tarea ya fue asignada a un usuario con el rol respectivo ***//

        if (isset($filterSendTask)) {
            return $filterSendTask;
        }
    }

    //*** Actualizar información del mapa ***//

    public function updateMapGeoJson(Request $request) {

        //*** Consultar variables de administracion, utilidad e iva  ***//

        $adminPorcentBudget = CvAdminPorcentBudget::first();

        if (empty($adminPorcentBudget)) {
            return response()->json(['message' => 'No se encutran porcentajes de administracion, utilidad e iva de forma global para generar presupuesto', 'code' => 409], 409);
        }

        if (CvTask::find($request->task_id)->task_sub_type_id == 29){
            $newCvGeoJson = CvGeoJson::where('task_id',$request->task_id)->first();
            $newCvGeoJson->geojson = $request->geojson;
            $newCvGeoJson->save();
        }else{
            $newCvGeoJson = CvGeoJson::where('task_id',$request->task_id)->first();
            $newCvGeoJson->geojson = $request->geojson;
            $newCvGeoJson->save();
            //--- Calcular presupuesto ---//
            if (isset($request->budget)) {

                //--- Verificar si existe un presupuesto relacionado a la tarea ---//
                $budgetExist = CvBudget::where("task_id", $request->task_id)->count();

                //--- Instancia de la clase del controlador de busqueda ---//
                $searchController = new GeneralSearchController();

                if ($budgetExist == 0) {

                    foreach ($request->budget as $dataBudget) {
                        //--- Validar cuando en el presupuesto se ingresa un STARD ---//
                        $material_id = (!empty($dataBudget["materialId"])) ? $dataBudget["materialId"] : 10;

                        //--- Consultar si el material y el presupuesto se encuentran relacionados ---//
                        $actionMaterial = CvBudgetActionMaterial::where("action_id", $dataBudget["actionId"])->where("budget_prices_material_id", $material_id)->first();

                        if (isset($actionMaterial->budgetPriceMaterial)) {
                            $length= $dataBudget["length"];

                            $valueBudget = $length  * $actionMaterial->budgetPriceMaterial->price;

                            //--- Verificar si existe el presupuesto con el mismo hash ---//
                            $budgetHash = CvBudget::where("hash_map", $dataBudget["hash"])->where("task_id", $request->task_id)->exists();

                            if ($budgetHash == false) {
                                //--- Guardar el registro del presupuesto ---//
                                $budget = new CvBudget();

                                $budget->value = round($valueBudget, 2);
                                $budget->length = $dataBudget["length"];
                                $budget->task_id = $request->task_id;
                                $budget->hash_map = $dataBudget["hash"];
                                $budget->action_material_id = $actionMaterial->id;

                                //*** Porcentajes ***/
                                $budget->administration = $adminPorcentBudget->administration;
                                $budget->utility = $adminPorcentBudget->utility;
                                $budget->iva = $adminPorcentBudget->iva;
                                
                                $budget->save();

                                $mantenimentForAction=CvMaintenanceForAction::where('actions_id',$actionMaterial->action->id);
                                
                                if($mantenimentForAction->exists()){
                                    $valueBudgetMantenimet=$dataBudget["length"] * CvBudgetPriceMaterial::find($mantenimentForAction->first()->manteniments_id)->price;

                                    $budget = new CvBudget();
                                    $budget->value = round($valueBudgetMantenimet, 2);
                                    $budget->length = $dataBudget["length"];
                                    $budget->task_id = $request->task_id;
                                    $budget->hash_map = $dataBudget["hash"];

                                    //*** Porcentajes ***/
                                    $budget->administration = $adminPorcentBudget->administration;
                                    $budget->utility = $adminPorcentBudget->utility;
                                    $budget->iva = $adminPorcentBudget->iva;
                                    
                                    $budget->action_material_id = $mantenimentForAction->first()->manteniments_id;
                                    $budget->save();

                                }
                            }
                        }
                    }
                }
            }

            //*** Consultar tarea para funciones como: ***//
            //--- 1. Cambiar su estado ---//

            $updateTask = CvTask::find($request->task_id);

            if (empty($updateTask)) {
                return [
                    "message" => "La tarea no existe en el sistema",
                    "response_code" => 200,
                ];
            }

            //--- Guardar cambios de tarea ---//

            $updateTask->save();

            //--- Registrar mapa a la tarea asignada ---//

            $newCvGeoJson = new CvGeoJson();

            $newCvGeoJson->geojson = $request->geojson;
            $newCvGeoJson->task_id = $request->task_id;

            //--- Consultar si la tarea ya tiene un mapa registrado ---//

            $CvGeoJsonExist = CvGeoJson::where("task_id", $request->task_id)->count();

            if ($CvGeoJsonExist == 1) {

                $updateCvGeoJson = CvGeoJson::where("task_id", $request->task_id)->first();

                $updateCvGeoJson->geojson = $request->geojson;

                if ($updateCvGeoJson->save()) {

                    //--- Guardar y actualizar informacion del buscador ---//
                    $this->infoSearchGeoJson($updateCvGeoJson->id);

                    $searchController = new \App\Http\Controllers\General\GeneralSearchController();
                    //--- Actualizar información del monitoreo para el buscador enviando el Id de la tarea ---//
                    $searchController->updateSearchMonitoringByTask($updateTask->id);


                    return [
                        "message" => "Registro actualizado",
                        "response_code" => 200
                    ];
                }
            }
        }
    }

    //--- Consultar el mapa de acuerdo a la tarea asignada ---//

    public function consultMapTask($id) {

        $geoJsonTask = CvGeoJson::where("task_id", $id)->first();

        if ($geoJsonTask == null) {
            $validategeoJsonTask = CvGeoJsonUser::where("task_id", $id)->where('user_id', $this->userLoggedInId());
            if ($validategeoJsonTask->exists()){
                $geoJsonTask = $validategeoJsonTask->first();
            }else{
                return "Para la tarea " . $id . " no se encuentra un predio";
            }
        }

        return $geoJsonTask->geojson;
    }

    // --- Aprobar mapa --- //

    public function approvedMap($id) {

        return CvTask::find($id);
    }

    //*** Consulta de procedimiento con geojson y presupuesto  ***//
    public function consultProcessGeojsonWithBudget($id) {

        $process = CvProcess::find($id);

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "response_code" => 200
            ];
        }

        $idTask = 0;
        $idProcess = 0;

        foreach ($process->processByTasks as $tasksProcess) {

            if ($tasksProcess->task_type_id == 1) {
                $idTask = $tasksProcess->id;
                $idProcess = $tasksProcess->pivot->process_id;
            }
        }

        //--- Validar que exista la tarea de medicion y su presupuesto ---//

        if ($idTask != 0) {

            $task = CvTask::find($idTask);

            $geojson = $task->geoJson[0]->geojson;
            $budget = $task->budget;

            $info = array();

            array_push($info, array(
                "geojson" => $geojson
            ));

            foreach ($budget as $dataBudget) {

                $budgetactionMaterial = CvBudgetActionMaterial::find($dataBudget->action_material_id);

                array_push($info, array(
                    "action_name" => $budgetactionMaterial->action->name,
                    "action_color" => $budgetactionMaterial->action->color,
                    "action_type" => $budgetactionMaterial->action->type,
                    "material_name" => $budgetactionMaterial->budgetPriceMaterial->name,
                    "material_type" => $budgetactionMaterial->budgetPriceMaterial->type
                ));
            }


            return $info;
        } else {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        }

        return $idProcess;
    }

    //*** Selecciona un hash del recorrido para mostrar las acciones y los materiales  ***//
    public function consultHashMaterialsActions($hash) {

        $budget = CvBudget::where("hash_map", $hash)->first();
        if(!$budget){
            return response()->json(["message" => "no existe material.", "code" => 500], 500);
        }
        $task = CvTask::find($budget->task->id);

        // $geojson = json_decode($task->geoJson[0]->geojson, true);

        $color = "";

        /*     foreach ($geojson["features"] as $infogeojson) {

                 if ($infogeojson["properties"]["hash"] == $hash) {

                     $color = $infogeojson["properties"]["FillColor"];
                 }
             }*/

        $info = array();

        $budgetactionMaterial = CvBudgetActionMaterial::find($budget->action_material_id);

        array_push($info, array(
            "color" => $budget->actionsMaterials->actionOne->color ? $budget->actionsMaterials->actionOne->color : $budget->actionsMaterials->actionOne->color_fill,
            "action_name" => $budgetactionMaterial->action->name,
            "action_type" => $budgetactionMaterial->action->type,
            "material_name" => $budgetactionMaterial->budgetPriceMaterial->name,
            "material_type" => $budgetactionMaterial->budgetPriceMaterial->type,
            "budget_id" => $budget->id
        ));

        return (!empty($info[0])) ? $info[0] : "";
    }

    //*** Filtrar informacion del usuario para el buscador ***//

    public function infoSearchGeoJson($map_geo_json_id) {

        if ($map_geo_json_id != 0) {

            $mapGeoJson = CvGeoJson::find($map_geo_json_id);

            $type = "Mapa";

            //--- Instancia del modelo del buscador universal con algolia ---//

            $searchAlgoliaController = new SearchAlgoliaController();

            //--- Informacion del predio obtenida en encuesta ---//

            $propertyName = "";
            $infoOwner = "";
            $actionsBudget = "";

            //--- Consultar procedimiento ---//
            $taskProcess = CvTask::find($mapGeoJson->task_id);

            if (!empty($mapGeoJson)) {

                if ($mapGeoJson->task->property_id != null) {

                    $property = CvProperty::find($mapGeoJson->task->property_id);

                    /*
                     * Buscar información en la encuesta para:
                     * 1. Nombre de predio
                     * 2. Datos de propietario
                     */

                    if ($property->info_json_general != null) {

                        $dataProperty = json_decode($property->info_json_general, true);
                        $propertyName = $dataProperty["property_name"];

                        $infoOwner = "Contacto: " .
                            "Cédula de Ciudadanía" . ": " . $dataProperty["contact"]["contact_id_card_number"] . ", " .
                            "Nombre" . ": " . $dataProperty["contact"]["contact_name"] . ", " .
                            "Correo electrónico" . ": " . $dataProperty["contact"]["contact_email"] . ", " .
                            "Celular" . ": " . $dataProperty["contact"]["contact_mobile_number"] . ", " .
                            "Teléfono" . ": " . $dataProperty["contact"]["contact_land_line_number"];
                    }

                    /*
                     * Buscar información de presupuesto del mapa:
                     * 1. Acciones
                     */

                    if (isset($taskProcess->budget) || empty($taskProcess->budget)) {

                        $arrayActions = array();

                        foreach ($taskProcess->budget as $item) {

                            array_push($arrayActions, CvBudgetActionMaterial::find($item->action_material_id)->actionOne->name);
                        }

                        $actionsBudget = implode(", ", $arrayActions);
                    }
                }
            }

            //--- Si existe presupuesto ---//

            ($actionsBudget != "") ? $description = $infoOwner . ", Acciones " . ": " . $actionsBudget : $description = $infoOwner;

            $dataSearch = [
                "name" => $propertyName . " - " . $taskProcess->title . " - " . $taskProcess->process[0]->name,
                "description" => $description,
                "type" => $type,
                "entity_id" => $mapGeoJson->task_id
            ];

            if ($searchAlgoliaController->registerSearchUniversal($dataSearch) == 200) {
                return true;
            }
        }
    }

    //*** Registrar los mapas de los guarda cuencas relacionado a una sola tarea ***//
    function registerMapsOfGuardBasinByTask(Request $request) {

        //--- Validar si existe una tarea de medicion ---//
        $task = CvTask::find($request->task_id);

        if (empty($task)) {
            return [
                "message" => "La tarea no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Validar que se encuentre en el sub tipo de "Medir predio" ---//
        if ($task->task_sub_type_id != 6 && $task->task_sub_type_id != 13 && $task->task_sub_type_id != 15 && $task->task_sub_type_id != 4) {

            return [
                "message" => "La tarea no se encuentra en estado de 'medir predio'",
                "code" => 500
            ];
        }


        //--- Consultar los usuarios que esta vinculados a la tarea ---//
        if (isset($task->taskUser) && count($task->taskUser) > 0 && !empty($task->taskUser)) {

            $geoJsonUserNotExists = false;

            foreach ($task->taskUser as $taskByUser) {

                //--- Validar que no se puede regitrar mas de una medioion por usuario a la misma tarea ---// 
                $existsGeoJsonUser = CvGeoJsonUser::where("user_id", $taskByUser->user_id)->where("task_id", $request->task_id);

                if ($this->userLoggedInId() == $taskByUser->user_id) {
                    if ($existsGeoJsonUser->exists() == false) {
                        $geoJsonUserNotExists = true;
                        break;
                    }
                }
            }

            if ($geoJsonUserNotExists == true && $taskByUser != "") {

                if ($this->userLoggedInId() == $taskByUser->user_id) {

                    $geoJsonTaskByUser = new CvGeoJsonUser();
                    $geoJsonTaskByUser->geojson = $request->geojson;
                    $geoJsonTaskByUser->user_id = $this->userLoggedInId();
                    $geoJsonTaskByUser->task_id = $request->task_id;

                    if ($geoJsonTaskByUser->save() == true) {

                        return [
                            "message" => "Registro exitoso",
                            "code" => 200
                        ];
                    } else {
                        return [
                            "message" => "Se ha presentado un error en el registro por favor intentelo de nuevo",
                            "code" => 500
                        ];
                    }
                }
            } else {

                $geoJsonTaskByUser = CvGeoJsonUser::where("user_id", $this->userLoggedInId())->where("task_id", $request->task_id)->first();
                if (empty($geoJsonTaskByUser)){
                    return [
                        "message" => "El usuario no tiene mapas",
                        "code" => 500
                    ];
                }
                $geoJsonTaskByUser->geojson = $request->geojson;
                $geoJsonTaskByUser->user_id = $this->userLoggedInId();
                $geoJsonTaskByUser->task_id = $request->task_id;
                $geoJsonTaskByUser->save();
                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            }
        }
    }

    /*
     * Registrar las mediciones de cada guarda cuenca al flujo original
     */

    //*** Registrar los mapas de segumiento tarea ***//
    function registerMapsSeguimentEquipmentByTask(Request $request) {
        //--- Validar si existe una tarea de medicion ---//
        $task = CvTask::find($request->task_id);
        if (empty($task)) {
            return [
                "message" => "La tarea no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Validar que se encuentre en el sub tipo de "Medir predio" ---//
        if ($task->task_sub_type_id != 6 && $task->task_sub_type_id != 13 && $task->task_sub_type_id != 15 && $task->task_sub_type_id != 4) {

            return [
                "message" => "La tarea no se encuentra en estado de 'medir predio'",
                "code" => 500
            ];
        }
        //--- Consultar los usuarios que esta vinculados a la tarea ---//
        if (isset($task->taskUser) && count($task->taskUser) > 0 && !empty($task->taskUser)) {
            //--- Validar que no se puede regitrar mas de una medioion por usuario a la misma tarea ---//
            $existsGeoJsonUser = CvGeoJsonUser::where("user_id", $this->userLoggedInId())->where("task_id", $request->task_id);
            if ($existsGeoJsonUser->exists()) {
                $geoJsonTaskByUser =  CvGeoJsonUser::find($existsGeoJsonUser->first()->id);
            } else {
                $geoJsonTaskByUser = new CvGeoJsonUser();
            }
            $geoJsonTaskByUser->geojson = $request->geojson;
            $geoJsonTaskByUser->user_id = $this->userLoggedInId();
            $geoJsonTaskByUser->task_id = $request->task_id;
            $validate_send=CvAllUserTasksMeasurement::where('user_id', $geoJsonTaskByUser->user_id)->where('task_id',$geoJsonTaskByUser->task_id);
            if ($validate_send->exists()){
                $send=$validate_send->first();
                $send->send=1;
                $send->save();
                $geoJsonTaskByUser->save();
                $all_task=CvAllUserTasksMeasurement::where('task_id',$geoJsonTaskByUser->task_id);
                if ($all_task->count() == CvAllUserTasksMeasurement::where('task_id',$geoJsonTaskByUser->task_id)->where('send',1)->count()){
                    $mapTaskGeoJson = $this->registerCoordinateForFlowTaskOfMap($request);
                    $task->task_sub_type_id=5;
                    $task->save();
                    return $mapTaskGeoJson;
                }else{
                    return [
                        "message" => "Mapa Almacenado, La tarea aun no pasa de subtipo",
                        "code" => 200
                    ];
                }
            }else{
                return [
                    "message" => "El usuario no puede cargar mapa",
                    "code" => 500
                ];
            }
        }else{
            return [
                "message" => "No hay usuarios asignados a la tarea",
                "code" => 500
            ];
        }
    }


    //
    public function unimMapAndnNextTask(Request $request)
    {
        //Funcion Global para unir mapas
        $this->registerCoordinateForFlowTaskOfMap($request);
        $task=CvTask::find($request->task_id);
        if ($task->task_sub_type_id == 4){
            $task->task_sub_type_id == 5;
            $task->save();

            $user_task=CvTaskUser::where('task_id',$task->id);
            if ($user_task->exists()){
                $user_task->delete();
            }
            $nes_user_task= new CvTaskUser();
            $nes_user_task->task_id= $request->task_id;
            $nes_user_task->user_id= User::where('role_id',6)->inRandomOrder()->first()->id;
            $nes_user_task->save();
        }
        return[
            "message"=>"Mapa unido",
            "code"=>200
        ];

    }

    public function registerCoordinateForFlowTaskOfMap($request) {

        $mapTaskGeoJson = $this->unionmapsgeomap($request);//Une los mapas

        $objectMapTaskGeoJson = new ArrayObject();
        $objectMapTaskGeoJson->setFlags(ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS);

        $objectMapTaskGeoJson->task_id = $request->task_id;
        $objectMapTaskGeoJson->state = 0;
        $objectMapTaskGeoJson->geojson = json_encode($mapTaskGeoJson, true);

        return $this->propertyGeoJsonTask($objectMapTaskGeoJson);
    }

    /**
     * @param $request
     */
    public function unionmapsgeomap($request)
    {
        $arrayCoordinatesDataGeoJson = array();

        //--- Obtener todas las mediciones de los guarda cuencas ---//
        if ($request->task_id > 0) {

            $getGeoJsonByUser = CvGeoJsonUser::where('task_id', $request->task_id)->get();

            foreach ($getGeoJsonByUser as $valueGetGeoJsonByUser) {

                //--- Cambiar la opcion a true cuando las mediciones de los guarda cuencas ya han sido utilizadas ---//
                $updateGeoJsonByUser = CvGeoJsonUser::find($valueGetGeoJsonByUser->id);
                $updateGeoJsonByUser->option = true;
                $updateGeoJsonByUser->save();

                $dataGeoJson = json_decode($valueGetGeoJsonByUser->geojson, true);

                foreach ($dataGeoJson["features"] as $features) {
                    array_push($arrayCoordinatesDataGeoJson, $features);
                }
            }
        }

        $mapTaskGeoJson = [
            "features" => $arrayCoordinatesDataGeoJson,
            "type" => "FeatureCollection"
        ];

        return $mapTaskGeoJson;
    }

    public function getFileData(){
        return Storage::disk('filedata')->get('data.json');
    }

}
