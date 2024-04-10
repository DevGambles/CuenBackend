<?php

namespace App\Http\Controllers\General;

use App\CvBudget;
use App\CvProcess;
use App\CvProcessByProjectActivity;
use App\CvProperty;
use App\CvTask;
use App\CvTaskProcess;
use App\CvTaskType;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\User;
use App\CvSubTypeTask;
use App\CvTaskStatus;
use App\CvLetterIntention;
use Carbon\Carbon;
use App\CvBackupFlowTasks;
use App\CvTaskTypeByActivity;
use App\CvGeoJsonUser;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\General\GeneralAssignTaskPersonalized;
use App\Http\Controllers\General\GeneralOneSignalController;
use App\Http\Controllers\General\GeneralEmailController;
use App\Http\Controllers\General\GeneralSubTypeTaskController;
use App\CvPotentialProperty;

class GeneralTaskController extends Controller {

    //--- Consultar las listas de las tareas de los usuarios logeados o por rol ---//

    public function listTasksByRol() {

        $listsTasks = CvTask::where('state', 0)
            ->where('task_sub_type_id', '>=', 4)
            ->orderBy('id', 'desc')
            ->get();

        switch ($this->userLoggedInRol()) {
            case 9:
            case 12:
            case 15:
            $listsTasks;
                break;
            default:
                $listsTasks = User::find($this->userLoggedInId())->task->where('task_sub_type_id', '>=', 4);
                break;
        }

        $info = array();

        foreach ($listsTasks as $listTask) {

            //--- Tareas de los usuarios ---//
            $infoTask = CvTask::find($listTask->id);
            $dateNowSubtract = $infoTask->where("id", $listTask->id)->whereBetween('date_end', [Carbon::now()->subDays(5), Carbon::now()])->exists();

            //--- Validar que la fecha de finalizacion de la tarea sea menor a la actual menos 5 dias ---//
            if ($infoTask->date_end < Carbon::now()->subDays(5)) {
                $infoTask->task_status_id = 3;
            }

            //--- Validar que la fecha de finalizacion se encuentre en un rango de 5 dias a la actual ---//
            if ($dateNowSubtract == true) {
                $infoTask->task_status_id = 2;
            }

            //--- Guardar los cambios del estado de la tarea ---//
            $infoTask->save();

            $listTask["task_status_name"] = CvTaskStatus::find($listTask->task_status_id)->name;
            $listTask["sub_type"] = CvSubTypeTask::find($listTask->task_sub_type_id);

            //*** Consultar predio de la tarea ***//
            $namePropertyPotentialArray = array();

            foreach ($listTask->process as $processPotentialItem) {
                //*** Predio ***//
                $propertyPotentialConsult = CvPotentialProperty::where("id", $processPotentialItem->potential_property_id)->first();
                array_push($namePropertyPotentialArray, $propertyPotentialConsult->property_name);
            }

            $namePropertyPotentialString = implode(",", $namePropertyPotentialArray);

            $listTask["property_name"] = $namePropertyPotentialString;

            if (isset($listTask->pivot)) {
                $listTask->pivot["process_id"] = $listTask->process[0]->pivot->process_id;
            }

            //--- Consultar informacion del usuario y rol  ---//
            if (isset($listTask->taskUser[0])) {

                $userRole = User::find($listTask->taskUser[0]["user_id"])->role;
                $listTask["users"] = $listTask->taskUser[0]->user;
                $listTask["role_id"] = $userRole->id;
                $listTask["role_name"] = $userRole->name;
            }

            $listTask["open"] = false;

            //--- Validar que el guarda cuenca no cuenta con mediciones en cv_geo_json_users ---//
            $geoJsonUsers = CvGeoJsonUser::where("user_id", $this->userLoggedInId())->where("task_id", $listTask->id)->exists();

            if ($listTask->state == 0) {
                //--- Filtrar por tareas activas ---//
                array_push($info, $listTask);
            }

            if ($listTask->sub_type->id == 32) {
                //--- Filtrar por tareas activas ---//
                array_push($info, $listTask);
            }
        }

        return $info;
    }

    // *** Consultar tareas por procesos *** //
    public function consultTaskByProcess($id) {

        /*
         *  Instancia clase para obtener las tareas y actualizar su informacion del usuario logueado o por rol 
         */

        $generalTaskController = new GeneralTaskController();

        $generalTaskController->listTasksByRol();

        //--- Tareas por procedimiento ---//
        $tasksProcess = array();

        $process = CvProcess::find($id);

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe",
                "response_code" => 200
            ];
        }

        foreach ($process->processByTasks->where('task_sub_type_id', '>=', 4) as $infoProcess) {

            /*
             * Mostrar información especifica o general de acuerdo al rol
             */

            if ($this->userLoggedInRol() != 3 && $this->userLoggedInRol() != 9 && $this->userLoggedInRol() != 15 && $this->userLoggedInRol() != 16 && $this->userLoggedInRol() != 10 && $this->userLoggedInRol() != 13 && $this->userLoggedInRol() != 17) {
                $tasks = User::find($this->userLoggedInId())->task;
            } else {
                $tasks = $process->processByTasks;
            }

            foreach ($tasks as $infoTasks) {
                $infoTasks["potential_property_id"] = $process->potential_property_id;
                $infoTasks["task_status_name"] = CvTaskStatus::find($infoTasks->task_status_id)->name;
                $infoTasks["task_sub_type_name"] = CvSubTypeTask::find($infoTasks->task_sub_type_id)->name;
                $infoTasks["type"] = "medicion";

                if ($infoTasks->pivot->task_id == $infoProcess->pivot->task_id) {

                    //--- Validar que el guarda cuenca no cuenta con mediciones en cv_geo_json_users ---//
                    $geoJsonUsers = CvGeoJsonUser::where("user_id", $this->userLoggedInId())->where("task_id", $infoProcess->pivot->task_id)->exists();
                    if ($geoJsonUsers == false) {
                        array_push($tasksProcess, $infoTasks);
                    }
                }
            }
        }

        foreach ($process->taskOpenProcess as $taskOpenProcess) {
            $taskOpenProcess["type"] = "abierta";
            $taskOpenProcess["open"] = true;
            $taskOpenProcess["task_sub_type_name"] = $taskOpenProcess->subtypes->name;
            array_push($tasksProcess, $taskOpenProcess);
        }

        if (!empty($tasksProcess)) {
            return $tasksProcess;
        } else {
            return [
                "message" => "El usuario no cuenta con mas tareas en el actual procedimiento",
                "response_code" => 200
            ];
        }

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe",
                "response_code" => 200
            ];
        }
    }

    // *** Consultar tipos de tareas por actividad *** //

    public function consultTypeTaskByActivity($id) {

        // --- Consultar procedimientos con sus respectivas actividades --- //

        $consultProcessActivities = CvProcessByProjectActivity::where("process_id", $id)->get();

        //--- Consultar si existe ya un procedimiento con carta de intencion ---//

        $letterIntention = CvLetterIntention::where("process_id", $id)->exists();

        if (count($consultProcessActivities) > 0) {

            $processActivitiesArray = array();

            // --- Consultar los tipos de tareas de acuerdo a las actividades del proceso --- //

            foreach ($consultProcessActivities as $processActivities) {

                $taskTypeByActivity = CvTaskTypeByActivity::where("activity_id", $processActivities->project_activity_id)->get();

                if (count($taskTypeByActivity) > 0) {
                    array_push($processActivitiesArray, $taskTypeByActivity);
                }
            }

            /*
             * Validar si las actividades cuentan con tipos de tareas relacionas
             */

            if (empty($processActivitiesArray)) {
                return [
                    "message" => "Las actividades seleccionadas en el procedimiento no cuentan con relaciones a los tipos de tarea",
                    "code" => 500
                ];
            }


            /*
             * Filtrar los tipos de actividades para que no se repitan
             */

            $arrayTypesTaskIds = array();

            foreach ($processActivitiesArray as $dataTypeByActivity) {
                foreach ($dataTypeByActivity as $value) {
                    array_push($arrayTypesTaskIds, $value->task_type_id);
                }
            }

            $uniqueArrayTypeTaskActivity = array_values(array_unique($arrayTypesTaskIds));

            /*
             * Tareas de procedimiento que ya cuenta con alguna de los tipos de tarea
             */

            $dataProcess = CvProcess::find($id)->processByTasks;

            $typesTasks = array();

            $taskProperty = "";

            foreach ($uniqueArrayTypeTaskActivity as $activityTypeValue) {

                $include = true;

                foreach ($dataProcess as $process) {

                    //--- Validar si ya existe tarea de medicion ---//

                    $taskMapValidate = true;

                    if ($process->task_type_id == 1) {
                        $taskMapValidate = false;
                    }

                    if ($process->task_type_id == $activityTypeValue || $letterIntention == true) {

                        $include = false;
                    }

                    //--- Obtener tarea de encuesta ---//

                    if ($process->task_type_id == 3) {
                        $taskProperty = $process;
                    }
                }

                if ($include) {
                    array_push($typesTasks, CvTaskType::find($activityTypeValue));
                }

                /*
                 * Validar si en las actividades existe el tipo de actividad de medicion
                 */

                (empty($typesTasks)) ? array_push($typesTasks, CvTaskType::find($activityTypeValue)) : $typesTasks;
            }

            /*
             * Personalizar respuesta:
             * 
             * 1. La tarea de medicion tiene que aparecer despues de que exista una tarea de encuesta y carta de intencion
             * 
             */

            if (!empty($typesTasks) && count($typesTasks) > 1) {
                foreach ($typesTasks as $item => $value) {

                    if ($value->id == 1) {

                        unset($typesTasks[$item]);
                        return array_values($typesTasks);
                    }
                }
            }

            /*
             *  Vaciar array de tipos de tarea cuando ya se valido
             */

            if ($taskMapValidate == false) {

                $typesTasks = array();

                return [
                    "message" => "El procedimiento ya cuenta con tareas de las actividades seleccionadas",
                    "response_code" => 500
                ];
            }

            /*
             * 2. Se puede crear la tarea de medicion cuando equipo de seguimiento apruebe la encuesta - Sub tipo Revisar encuenta
             */

            if ($taskProperty->task_sub_type_id >= 2 && $taskMapValidate == true) {

                return array_values($typesTasks);
            } else {

                return [
                    "message" => "Aun no se puede obtener el tipo de tarea de medicion hasta que la tarea de encuesta se finalice",
                    "response_code" => 500
                ];
            }
        }

        return [
            "message" => "El procedimiento no existe",
            "response_code" => 500
        ];
    }

    // *** Corregir esta ruta -> Tipos de actividad por procedimientos *** //

    public function consultTypeTaskByActivityProcess($id) {

        // --- Consultar procedimientos con sus respectivas actividades --- //

        $consultProcessActivities = CvProcessByProjectActivity::where("process_id", $id)->get();

        if (count($consultProcessActivities) > 0) {

            // --- Consultar predios potenciales --- //

            $properties = CvProperty::get();

            //--- Validar si hay o no un predio potencial registrado ---//

            $existProperty = 0;

            foreach ($properties as $property) {

                if ($property->main_coordinate != null) {
                    $existProperty = 1;
                }
            }

            // --- Guardar los tipos de tarea de acuerdo al procedimiento seleccionado --- //
            $typesTasks = array();

            if ($existProperty == 1) {

                // --- Consultar los tipos de tareas de acuerdo a las actividades del proceso --- //

                $taskIdProperty = 0;

                foreach ($consultProcessActivities as $processActivities) {

                    $typeTask = CvTaskTypeByActivity::where("activity_id", $processActivities->project_activity_id)->first();

                    if ($typeTask != null) {
                        array_push($typesTasks, $typeTask->task_type_id);
                    }

                    if ($typeTask->id == 3) {
                        $taskIdProperty = 1;
                    }
                }
            }

            //--- Obtener los datos unicos de la pivote y enviar la informacion del tipo de la tarea ---//

            $typeTaskId = array_unique(array_values($typesTasks));

            //--- Reiniciar array ---//

            $typesTasks = array();

            array_push($typesTasks, CvTaskType::find($typeTaskId[0]));

            return $typesTasks;
        }

        return [
            "message" => "El procedimiento no existe",
            "response_code" => 200
        ];
    }

    //*** Aprobar tarea ***//

    public function approvedTask(Request $request) {

        $task = CvTask::find($request->task_id);

        if (empty($task)) {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 500
            ];
        }

        // --- Tipo de aprobacion --- //
        $typeApproved = "";
        $typeTask = "";
        $infoTask = "";

        switch ($this->userLoggedInRol()) {

            case 2:
                switch ($task->task_sub_type_id) {

                    case 2:
                        $typeTask = "Register_task_property";
                        $typeApproved = "approve_task_property";
                        break;

                    case 10:
                        $typeTask = "General_task";
                        $typeApproved = "approve_task_property";
                        break;

                    case 20:
                        $typeTask = "GeoJson_task";
                        $typeApproved = "task_minuta_direction";
                        break;
                }

            case 3:

                switch ($task->task_sub_type_id) {

                    case 14:

                        $typeTask = "GeoJson_task";
                        $typeApproved = "task_minuta_legal";
                        $infoTask = $task->geoJson;
                        break;
                }

                break;

            case 7:

                switch ($task->task_sub_type_id) {

                    case 5:

                        $typeTask = "GeoJson_task";
                        $typeApproved = "approve_task_budget";
                        $infoTask = $task->geoJson;
                        break;

                    case 6:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json";
                        $infoTask = $task->property;
                        break;

                    case 11:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json_team";
                        $infoTask = $task->property;
                        break;

                    case 13:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json";
                        $infoTask = $task->property;
                        break;
                }

                break;

            case 6:

                switch ($task->task_sub_type_id) {

                    case 12:

                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_coordination";
                        $infoTask = $task->property;
                        break;

                    case 11:

                        $typeTask = "GeoJson_task";
                        $typeApproved = "permission_geo_json_team";
                        $infoTask = $task->property;

                        break;

                    case 5:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json_team";
                        if (!CvBudget::where('task_id', $request->task_id)->exists()) {
                            return [
                                "message" => "No se ha generado un presupuesto",
                                "code" => 500
                            ];
                        }
                        $infoTask = $task->property;

                        break;

                    case 15:
                        $typeTask = "General_task";
                        $typeApproved = "coordination_resource_good_practices";
                        $infoTask = $task->property;
                        break;

                    case 29:
                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_coordination";
                        $infoTask = $task->property;
                        break;
                }
                break;

            case 8:

                switch ($task->task_sub_type_id) {

                    case 22:

                        $typeTask = "General_task";
                        $typeApproved = "task_map_validation";
                        $infoTask = $task->property;
                        break;

                    case 28:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json";
                        $infoTask = $task->property;
                        break;


                    default :
                        $typeTask = "Register_task_property";
                        $typeApproved = "permission_poll";
                        $infoTask = $task->property;
                        break;
                }

                break;

            case 11:

                switch ($task->task_sub_type_id) {

                    case 26:

                        $typeTask = "General_task";
                        $typeApproved = "coordination_resource_good_practices";
                        break;

                    default:

                        $typeTask = "General_task";
                        $typeApproved = "permission_geo_json";

                        break;
                }
                break;

            case 12:

                switch ($task->task_sub_type_id) {

                    case 16:
                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_coordination";
                        break;

                    case 25:

                        $typeTask = "General_task";
                        $typeApproved = "task_financial";
                        break;
                }

                break;

            case 4:

                switch ($task->task_sub_type_id) {

                    case 32:
                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_coordination";
                        break;
                }

                break;

            //--- Apoyo de coordinacion -> RBP y RH ---//

            case 9:
            case 15:

                switch ($task->task_sub_type_id) {

                    case 24:

                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_direction";
                        $infoTask = $task->geoJson;
                        break;

                    case 27:

                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_legal";
                        $infoTask = $task->geoJson;
                        break;
                }

                break;

            case 10:
            case 16:

                switch ($task->task_sub_type_id) {

                    case 24:

                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_direction";
                        $infoTask = $task->geoJson;
                        break;

                    case 27:

                        $typeTask = "General_task";
                        $typeApproved = "task_minuta_legal";
                        $infoTask = $task->geoJson;
                        break;
                }

                break;
        }

        //--- Cambiar el sub tipo de la tarea ---//
        $subType = new GeneralSubTypeTaskController();

        $valueSubType = $subType->subTypeTask($this->userLoggedInRol(), $task->task_type_id, $task->task_sub_type_id);

        if (is_array($valueSubType)) {
            return $valueSubType;
        }

        //--- Se capturo una excepción al asginar un subtipo ---//

        if (is_string($valueSubType)) {
            return $valueSubType;
        }

        $task->task_sub_type_id = ($valueSubType != null) ? $valueSubType : $task->task_sub_type_id;

        //--- Asignarle la tarea a rol de correspondiente ---//

        $info = array(
            //--- Información para el filtro de las rutas automaticas ---//
            "permission_route" => $typeApproved,
            "task_id" => $task->id,
            "task_type_id" => $task->task_type_id,
            "task_status_id" => $task->task_status_id,
            "option" => $request->option
        );

        //--- Funcion para filtrar los roles que se van a encargar de la tarea enviada ---//
        $filterSendTask = $this->routesAutomatics($info);

        if (isset($filterSendTask["user"]) && isset($filterSendTask["task"])) {

            $this->notificationApprovedTask($task);
            $this->notificationEmailApprovedTask($task, $filterSendTask["user"]);

            /*
             * Asignar la tarea a mas de un usuario
             */

            $generalAssignTaskPersonalized = new GeneralAssignTaskPersonalized();
            $generalAssignTaskPersonalized->assignTaskBudgetRbpRh($task->id, $filterSendTask["user"]);

            //--- Guardar la información de la tarea y envio de notificaciones OneSignal - Email ---//

            if ($task->save()) {

                //--- Enviar al historial ---//
                $historyTask = array();

                if ($typeTask == "General_task") {

                    array_push($historyTask, array(
                        "type_task" => $typeTask,
                        "info" => $infoTask,
                        "map" => $task->geoJson,
                        "property" => $task->property,
                        "status" => $task->task_status_id,
                        "task_id" => $task->id,
                        "user_from" => $this->userLoggedInId(),
                        "user_to" => $filterSendTask["user"]
                            )
                    );
                } else {
                    array_push($historyTask, array(
                        "type_task" => $typeTask,
                        "info" => $infoTask,
                        "status" => $task->task_status_id,
                        "task_id" => $task->id,
                        "user_from" => $this->userLoggedInId(),
                        "user_to" => $filterSendTask["user"]
                            )
                    );
                }

                // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
                $historyController = new GeneralHistoryTaskController();

                if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                    // --- Validar si el usuario puede aprobar --- //
                    if ($typeApproved != "") {

                        return [
                            "message" => "Tarea ha sido enviada",
                            "response_code" => 200
                        ];
                    } else {
                        return [
                            "message" => "Usuario no cuenta con el rol respectivo",
                            "response_code" => 200
                        ];
                    }
                }
            }
        }

        if (isset($filterSendTask)) {
            return $filterSendTask;
        }
    }

    //*** Solicitar certificado de tradicion ***//

    public function requestTraditionCertificate($id) {

        $task = CvTask::find($id);

        // --- Validar que el rol del usuario permitido para realizar la accion --- //

        if ($this->userLoggedInRol() == 2) {

            if ($task->task_sub_type_id == 2) {

                // --- Cambiar el sub tipo de la tarea --- //

                $task->task_sub_type_id = 8;
                $task->save();

                //--- Asignarle la tarea a rol de sig ---//

                $info = array(
                    //--- Información para el filtro de las rutas automaticas ---//
                    "permission_route" => "request_tradition_certificate",
                    "task_id" => $task->id,
                    "task_type_id" => $task->task_type_id,
                    "task_status_id" => $task->task_status_id
                );

                //--- Funcion para filtrar los roles que se van a encargar de la tarea enviada ---//
                $filterSendTask = $this->routesAutomatics($info);

                //--- Guardar historial de la tarea ---//
                if (isset($filterSendTask["user"]) && isset($filterSendTask["task"])) {

                    $taskController = new TaskController();

                    $historyTask = array();

                    // --- Validar si la tarea cuenta con informacion de mapa y encuesta --- //
                    $map = $task->geoJson;
                    $property = $task->property;

                    array_push($historyTask, array(
                        "type_task" => "Back_task",
                        "info" => $taskController->show($id),
                        "map" => $map,
                        "property" => $property,
                        "task_id" => $id,
                        "user_from" => $this->userLoggedInId(),
                        "user_to" => $filterSendTask["user"]
                            )
                    );

                    // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
                    $historyController = new GeneralHistoryTaskController();

                    if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                        return [
                            "message" => "Registro exitoso",
                            "response_code" => 200
                        ];
                    }
                }
            }

            return [
                "message" => "La tarea ya ha sido enviada al usuario respectivo de guarda cuenca",
                "response_code" => 200,
            ];
        } else {

            return [
                "message" => "El usuario no cuenta con el rol permitido para solicitar el certificado de tradicion",
                "response_code" => 200,
            ];
        }
    }

    //*** Enviar tarea a juridico ***//

    public function sendTaskTraditionCertificate($id) {

        $task = CvTask::find($id);

        $filterSubType = 8;

        if (!empty($task)) {

            if ($task->task_sub_type_id == $filterSubType) {

                //--- Asignarle la tarea a rol de juridico ---//

                $info = array(
                    //--- Información para el filtro de las rutas automaticas ---//
                    "permission_route" => "task_juridico",
                    "task_id" => $task->id,
                    "task_type_id" => $task->task_type_id,
                    "task_status_id" => $task->task_status_id
                );

                //--- Funcion para filtrar los roles que se van a encargar de la tarea enviada ---//
                $filterSendTask = $this->routesAutomatics($info);

                //--- Guardar historial de la tarea ---//
                if (isset($filterSendTask["user"]) && isset($filterSendTask["task"])) {

                    $task->task_sub_type_id = 9;
                    $task->save();

                    $taskController = new TaskController();

                    $historyTask = array();

                    // --- Validar si la tarea cuenta con informacion de mapa y encuesta --- //

                    $map = $task->geoJson;
                    $property = $task->property;

                    array_push($historyTask, array(
                        "type_task" => "Back_task",
                        "info" => $taskController->show($id),
                        "map" => $map,
                        "property" => $property,
                        "task_id" => $id,
                        "user_from" => $this->userLoggedInId(),
                        "user_to" => $filterSendTask["user"]
                            )
                    );

                    // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
                    $historyController = new GeneralHistoryTaskController();

                    if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                        return [
                            "message" => "Registro exitoso",
                            "response_code" => 200
                        ];
                    }
                }
            } else {

                return [
                    "message" => "La tarea no cuenta con el sub tipo de: " . CvSubTypeTask::find($filterSubType)->name . " para realizar esta operacion",
                    "response_code" => 200,
                ];
            }
        }

        return [
            "message" => "La tarea no existe en el sistema",
            "response_code" => 200,
        ];
    }

    //--- Listar las tareas por usuario prontas a vencer ---//

    public function listsTaskByUserSoonOvercome() {
        
        $userByTask = User::find($this->userLoggedInId());

        $this->listTasksByRol();

        //--- Tareas de los usuarios ---//
        $dateTaskDesc = new Collection();

        if (!empty($userByTask->task())) {

            $minDateConsult = $userByTask->task()->orderBy("date_end", "Asc")->first();

            if (!empty($minDateConsult)) {

                $minDate = $minDateConsult->date_end;

                $dateTaskDesc = $userByTask->task()
                        ->whereBetween('task_status_id', [2, 3])
                        ->where('task_sub_type_id', '!=', 33)
                        ->limit(5)
                        ->get();

                $dateTaskDesc = $userByTask->task()
                        ->limit(3)
                        ->get();

                foreach ($dateTaskDesc as $task) {
                    $idPotentialProperty = null;
                    $namePotentialProperty = null;
                    
                    $process = CvProcess::find(CvTaskProcess::where('task_id', $task->id)->first()->process_id);

                    $idPotentialProperty = $process->potentialProperty->id;
                    $namePotentialProperty = $process->potentialProperty->property_name;
                    $task['process'] = $process;
                    $task["task_type_name"] = $task->taskSubType->name;
                    $task["task_status_name"] = CvTaskStatus::find($task->task_status_id)->name;
                    $task["type"] = 'medicion';
                    $task["potential_detail"] = [
                        'id' => $idPotentialProperty,
                        'name' => $namePotentialProperty
                    ];
                }

                $collectionTaskExecution = new Collection();
                foreach ($userByTask->taskExecutionByUser as $item) {
                    $collectionTaskExecution->prepend($item->taskExecution);
                }

                $minDateTaskExecution = $collectionTaskExecution->sortBy('date_end')->first();


                if (!empty($minDateTaskExecution)) {

                    $dataRaskExecution = $collectionTaskExecution
                            ->where('task_status_id', '>=', 2)
                            ->where('task_status_id', '<=', 3)
                            ->where('task_status_id', '!=', 17)
                            ->slice(0, 3);

                    foreach ($dataRaskExecution as $taksExecution) {
                        $propertyName = null;
                        $propertyid = null;

                        try {
                            $potentialProperty = $taksExecution->poolByProcess->poolProcess->Process->potentialProperty;
                            if ($potentialProperty) {
                                $propertyName = $potentialProperty->property_name;
                                $propertyid = $potentialProperty->id;
                            }
                        } catch (Exception $e) {
                            
                        }
                        $taksExecution['task_type_name'] = $taksExecution->subtypes->name;
                        $taksExecution['task_status_name'] = $taksExecution->status->name;
                        $taksExecution['type'] = 'ejecucion';
                        $taksExecution["potential_detail"] = [
                            'id' => $propertyid,
                            'name' => $propertyName
                        ];
                        $dateTaskDesc->prepend($taksExecution);
                    }
                }
            }
            $dateTaskDesc = $this->taskOpenAssigment($userByTask, $dateTaskDesc);
            $result = $dateTaskDesc->sortByDesc('id');
            return $result->values()->slice(0, 3);
            return [
                "message" => "El usuario no cuenta con tareas en el sistema",
                "response_code" => 500,
            ];
        }
    }

    //*** Listado de las tareas que intervino el usuario autenticado ***//

    public function consultListTaskHistoryUser() {

        $backupTask = CvBackupFlowTasks::where("info_user_to", $this->userLoggedInId())->get();

        //--- Personalizar respuesta ---//

        $info = [];

        foreach ($backupTask as $value) {

            $task = CvTask::find($value->info_task_id);

            array_push($info, $task);
        }

        $task = array_unique($info);

        //--- Personalizar respuesta ---//

        if (!empty($info)) {
            foreach ($info as $value) {
                $value["sub_type_name"] = $value->taskSubType->name;
                $value["task_status_name"] = $value->taskStatus->name;
                $value["task_type_name"] = $value->taskType->name;
            }
        }

        return (!empty($info)) ? array_values($task) : [];
    }

    //*** Enviar notificaciones cada vez que se aprueba la tarea ***//

    public function notificationApprovedTask($task) {

        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        //--- Validar para tarea de predio potencial y tarea general ---//

        $processString = (isset($task->process)) ? "Procedimiento: " . $task->process[0]->name : "";

        $content = "Se le asigno una nueva tarea:  " . $task->taskSubType->name . ". Procedimiento: " . $processString;

        $sendNotificationCount = 0;

        foreach ($task->user as $users) {
            $sendNotificationCount++;
            $oneSignal->notificationTask($users->id, $task->id, $content, "general");
        }
    }

    //*** Enviar email cada vez que se aprueba la tarea ***//

    public function notificationEmailApprovedTask($task, $userId) {

        //--- Enviar correo electronico al usuario que se le asigno la tarea ---//

        $emailController = new GeneralEmailController();

        //--- Parametros para la funcion email ---//

        $view = "emails.task_assigned";


        //--- Validar para tarea de predio potencial y tarea general ---//

        $processString = (isset($task->process)) ? "Procedimiento: " . $task->process[0]->name : "";

        $userTask = User::find($userId);

        $infoEmail = array(
            "email" => $userTask->email,
            "subject" => "Asignación de una nueva tarea",
            "title" => "Asignación de una nueva tarea",
            "type" => $task->taskType->name,
            "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se "
            . "le ha asignado una nueva tarea en el flujo con el sub tipo " . $task->taskSubType->name . " para continuar con su proceso en el procedimiento "
            . "de " . $processString . "."
        );

        $emailController->sendEmail($view, $infoEmail);
    }

    public function getProperties($cant, $userByTask) {

        $collectionPropertiesByUser = new Collection();
        $propertiesByUser = $userByTask->potentialPropertiesByUser;
        foreach ($propertiesByUser as $propertyByUser) {
            $property = $propertyByUser->property;

            if ($property) {
                if ($property->property_psa == 1)
                    $property['potential_sub_type'] = 'PSA';
                else {
                    $property['potential_sub_type'] = $property->potentialPropertySubType->name;
                }
                $collectionPropertiesByUser->prepend($property);
            }
        }

        return $collectionPropertiesByUser->sortBy('created_at')->slice(0, $cant);
    }

    /**
     * @param $userByTask
     * @param $dateTaskDesc
     */
    private function taskOpenAssigment($userByTask, $dateTaskDesc) {
        $dataTaskOpens = $userByTask->taskOpen()
                ->whereBetween('task_status_id', [1, 2, 3])
                ->where('task_open_sub_type_id', '!=', [5, 17, 25, 30])
                ->limit(3)
                ->get();

        foreach ($dataTaskOpens as $dataTaskOpen) {
            $tipe = $dataTaskOpen->subtypes->name;
            $dataTaskOpen->process;

            if ($dataTaskOpen->process->type_process == 'erosion' & $dataTaskOpen->users->role->id == 3) {
                $tipe = 'Asignar a guardacuenca';
            }

            $dataTaskOpen['task_type_name'] = $tipe;
            $dataTaskOpen['task_status_name'] = $dataTaskOpen->status->name;
            $dataTaskOpen['type'] = 'abierta';
            $dateTaskDesc->prepend($dataTaskOpen);
            ;
        }
        return $dateTaskDesc;
    }

}
