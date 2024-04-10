<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\CvTask;
use App\CvProcess;
use App\CvTypeMonitoring;
use App\CvMonitoring;
use App\CvMonitoringFile;
use App\CvMonitoringPoint;
use App\CvMonitoringComment;
use App\CvMonitoringByFlow;
use App\CvMonitoringByUser;
use App\CvBudgetActionMaterial;
use Illuminate\Support\Facades\File;
use App\CvBackupFlowMonitoring;
use App\CvBudget;
use App\Http\Controllers\Search\SearchAlgoliaController;

class GeneralMonitoringController extends Controller {

    // *** Consultar todos los monitoreos ***//

    public function index() {

        //--- Consulta de todos los monitoreos registros en el sistema ---//

        switch ($this->userLoggedInId()) {

            case 3:
                $monitorings = CvMonitoring::get();
                break;

            default:

                $monitorings = CvMonitoring::where("user_id", $this->userLoggedInId())->orWhere('user_id_creator', $this->userLoggedInId())->get();

                break;
        }

        foreach ($monitorings as $monitoring) {

            $monitoring->commentByMonitoring;
            $monitoring->process->name;

            if (!empty($monitoring->commentByMonitoring)) {

                $monitoring["start"] = substr($monitoring->date_start, 0, 10);
                $monitoring["end"] = substr($monitoring->date_deadline, 0, 10);

                foreach ($monitoring->commentByMonitoring as $index => $commentMonitoring) {

                    if (empty($monitoring->title)) {
                        $monitoring["title"] = $monitoring->process->name;
                    } else {
                        $monitoring["title"] = $monitoring->title;
                    }

                    $monitoring["type_monitoring"] = CvTypeMonitoring::find($monitoring->type_monitoring_id)->name;
                    $monitoring["type_monitoring"] = CvTypeMonitoring::find($monitoring->type_monitoring_id)->name;


                    //--- Informacion especifica del usuario que realizo el comentario ---//

                    if ($commentMonitoring->user_id != null) {
                        $user = User::find($commentMonitoring->user_id);
                        $commentMonitoring["user_name"] = $user->name;
                        $commentMonitoring["user_role"] = $user->role->name;
                    }

                    if (!empty($monitoring->userByMonitoring)) {
                        $monitoring["user_id"] = $monitoring->userByMonitoring->id;
                        $monitoring["role_id"] = $monitoring->userByMonitoring->role_id;
                    }

                    //--- Eliminar comentarios de punto ---//
                    if ($commentMonitoring->monitoring_point_id != null) {

                        unset($monitoring->commentByMonitoring[$index]);
                    }

                    unset($monitoring->process);
                    unset($monitoring->state);
                    unset($monitoring->created_at);
                    unset($monitoring->updated_at);
                    unset($monitoring->commentByMonitoring->pivot);
                    unset($monitoring->commentByMonitoring->created_at);
                    unset($monitoring->commentByMonitoring->updated_at);
                    unset($monitoring->userByMonitoring);
                }
            }
        }

        return $monitorings;
    }

    // *** Ruta por defecto de laravel - retorna error 404 ***//

    public function create() {
        abort(404);
    }

    // *** Creacion de monitoreos por procedimiento obteniendo la tarea como parametro ***//

    public function store(Request $request) {

        //--- Informacion de la tarea ---//
        $task = CvTask::find($request->task_id);

        if (!empty($task)) {

            //--- Consultar las tareas que cuenta con el procedimiento ---//
            $processWithTask = CvProcess::find($task->process[0]->id)->processByTasks;

            //--- Consultar la tarea que tenga medicion de predio ---//

            $idTask = 0;
            $idProcess = 0;

            foreach ($processWithTask as $tasksProcess) {

                if ($tasksProcess->task_type_id == 1) {
                    $idTask = $tasksProcess->id;
                    $idProcess = $tasksProcess->pivot->process_id;
                }
            }

            //--- Consultar si la tarea de medicion de mapa cuenta con presupuesto ---//

            if ($idTask != 0) {

                $mapGeoTask = CvTask::find($idTask)->budget;

                if (count($mapGeoTask) > 0) {

                    //--- Registrar monitoreo ---//
                    $monitoring = new CvMonitoring();

                    $monitoring->date_start = $request->date_start;
                    $monitoring->date_deadline = $request->date_deadline;
                    $monitoring->process_id = $idProcess;
                    $monitoring->type_monitoring_id = $request->type_monitoring_id;
                    $monitoring->user_id_creator = $this->userLoggedInId();

                    if ($monitoring->save()) {

                        //--- Registrar comentarios del monitoreo ---//
                        $comment = new CvMonitoringComment();
                        $comment->description = $request->comment;
                        $comment->monitoring_id = $monitoring->id;
                        $comment->user_id = $this->userLoggedInId();

                        if ($comment->save()) {

                            //--- Historial del monitoreo ---//
                            $this->backFlowMonitoring($monitoring->id, $this->userLoggedInId(), null);

                            //--- Guardar información de monitoreo en algolia ---//

                            $this->infoSearchMonitoring($monitoring->id);

                            return [
                                "message" => "Registro exitoso",
                                "response_code" => 200
                            ];
                        }
                    }
                } else {

                    return [
                        "message" => "La tarea no cuenta aun con presupuesto",
                        "response_code" => 200
                    ];
                }
            }
        } else {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    // *** Consultar información especifica del monitoreo ***//

    public function show($id) {

        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Consultar el procedimiento del monitoreo y validar que cuente con la tarea de medicion ---//

        $taskMap = 0;

        $process = CvProcess::find($monitoring->process->id);

        foreach ($process->processByTasks as $processByTasks) {

            if ($processByTasks->task_type_id == 1) {
                $taskMap = $processByTasks->id;
            }
        }

        //--- Consultar si la tarea cuenta con un geo json ---//

        if ($taskMap != 0) {

            $task = CvTask::find($taskMap);

            if (!empty($task)) {

                if (isset($task->geoJson) && isset($task->budget)) {

                    $geojson = json_decode($task->geoJson[0]->geojson, true);

                    $info = array();
                    $user = array();
                    $map = array();

                    //--- Filtrar los comentarios por monitoreo ---//
                    foreach ($monitoring->commentByMonitoring as $index => $commentMonitoring) {

                        //--- Eliminar comentarios de punto ---//
                        if ($commentMonitoring->monitoring_point_id != null) {

                            unset($monitoring->commentByMonitoring[$index]);
                        }
                    }

                    //--- Informacion general ---//

                    array_push($info, array(
                        "id" => $monitoring->id,
                        "title" => $monitoring->title,
                        "hash_map" => $monitoring->hash_map,
                        "start" => $monitoring->date_start,
                        "end" => $monitoring->date_deadline,
                        "date_start" => $monitoring->date_start,
                        "date_deadline" => $monitoring->date_deadline,
                        "procedure_id" => $monitoring->process_id,
                        "type_monitoring" => $monitoring->typeMonitoring->name,
                        "comment_by_monitoring" => $monitoring->commentByMonitoring
                    ));

                    if (!empty($monitoring->userByMonitoring)) {

                        array_push($user, array(
                            "user" => array(
                                "names" => $monitoring->userByMonitoring->names,
                                "role_id" => $monitoring->userByMonitoring->role_id,
                                "last_names" => $monitoring->userByMonitoring->last_names,
                                "name" => $monitoring->userByMonitoring->name,
                                "email" => $monitoring->userByMonitoring->email
                            )
                        ));
                    }

                    //--- Consultar los puntos del monitoreo con sus archivos y comentarios ---//

                    $pointsFiles = array();

                    foreach ($monitoring->pointByMonitoring as $points) {

                        array_push($pointsFiles, array(
                            "id" => $points->id,
                            "coordinate" => $points->name,
                            "images" => CvMonitoringPoint::find($points->id)->pointFilesMonitoring,
                            "comments" => CvMonitoringComment::where("monitoring_point_id", $points->id)->get()
                        ));
                    }

                    foreach ($geojson["features"] as $infogeojson) {
                        foreach ($task->budget as $budget) {

                            $budgetactionMaterial = CvBudgetActionMaterial::find($budget->action_material_id);

                            if ($infogeojson["properties"]["hash"] == $monitoring->hash_map) {

                                array_push($map, array(
                                    "geojson_feature" => json_encode($infogeojson),
                                    "points" => $pointsFiles,
                                    "budget" => array(
                                        "action_name" => $budgetactionMaterial->action->name,
                                        "action_type" => $budgetactionMaterial->action->type,
                                        "material_name" => $budgetactionMaterial->budgetPriceMaterial->name,
                                        "material_type" => $budgetactionMaterial->budgetPriceMaterial->type
                                    )
                                ));

                                break;
                            }
                        }
                    }

                    //--- Validacion de las diferentes respuestas que se puede obtener al consultar un monitoreo ---//

                    if (isset($info[0]) && isset($map[0])) {
                        $totalInfo = array_collapse([$info[0], $user[0], $map[0]]);
                    } elseif (isset($user[0])) {
                        $totalInfo = array_collapse([$info[0], $user[0]]);
                    } else {
                        $totalInfo = array_collapse($info[0]);
                    }

                    return (!empty($totalInfo)) ? $totalInfo : $totalInfo;
                } else {
                    return [
                        "message" => "La tarea no cuenta con el registro de mapa o presupuesto",
                        "response_code" => 200
                    ];
                }
            } else {
                return [
                    "message" => "La tarea no existe en el sistema",
                    "response_code" => 200
                ];
            }
        }
        return [
            "message" => "La tarea no cuenta mapa",
            "response_code" => 200
        ];
    }

    //*** Ruta por defecto de laravel - retorna error 404 ***//

    public function edit($id) {
        abort(404);
    }

    //*** Actualizar el monitoreo desde web siempre y cuando no se presente un recorrido ***//

    public function update(Request $request, $id) {

        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring) > 0) {

            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 500
            ];
        }

        if (count($monitoring->pointByMonitoring) > 0) {

            return [
                "message" => "El monitoreo no se puede actualizar ya que se encuentra en proceso de recoleccion de informacion de puntos",
                "response_code" => 500
            ];
        }

        //--- Actualizar informacion general ---//

        $monitoring->title = $request->title;
        $monitoring->date_start = $request->date_start;
        $monitoring->date_deadline = $request->date_deadline;
        $monitoring->type_monitoring_id = $request->type_monitoring_id;
        $monitoring->hash_map = $request->hash;
        $monitoring->user_id = $request->user_id;

        if ($monitoring->save()) {

            //--- Guardar información de monitoreo en algolia ---//

            $this->infoSearchMonitoring($monitoring->id);

            return [
                "message" => "Registro actualizado",
                "response_code" => 200
            ];
        }
    }

    //*** Eliminar monitoreo cuando el no se le haya asignado usuario ***//

    public function destroy($id) {

        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 500
            ];
        }

        if (!empty($monitoring->userByMonitoring)) {
            return [
                "message" => "El monitoreo no puede ser eliminado ya que se encuentra asignado a un usuario",
                "response_code" => 500
            ];
        }

        //--- Eliminar los comentarios del monitoreo ---//
        foreach ($monitoring->commentByMonitoring as $commentMonitoring) {
            $commentMonitoring->delete();
        }

        if ($monitoring->delete()) {

            return [
                "message" => "Monitoreo eliminado",
                "response_code" => 200
            ];
        }
    }

    //*** Consultar todos los tipos de monitoreo ***//

    public function consultAllTypesMonitoring() {

        return CvTypeMonitoring::consultAllTypesMonitoring();
    }

    //*** Registrar comentarios para un monitorio o punto ***//

    public function registerCommentMonitoring(Request $request) {

        //--- Validar si existe el monitoreo ---//
        $monitoring = CvMonitoring::find($request->monitoring_id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Validar si existe el punto ---//

        if (isset($request->point_id)) {
            $point = CvMonitoringPoint::find($request->point_id);

            if (empty($point)) {
                return [
                    "message" => "El punto no existe en el sistema",
                    "response_code" => 200
                ];
            }
        }

        if (count($monitoring->commentByMonitoring) > 0) {

            $commentMonitoring = new CvMonitoringComment();
            $commentMonitoring->description = $request->comment;

            //--- Validar si el comentario que se va a registrar es de punto o monitoreo ---//

            if (isset($request->monitoring_id)) {

                $commentMonitoring->monitoring_id = $request->monitoring_id;
            } else {
                $commentMonitoring->point_id = $request->point_id;
            }
            $commentMonitoring->user_id = $this->userLoggedInId();

            if ($commentMonitoring->save()) {
                return [
                    "message" => "Registro exitoso",
                    "response_code" => 200
                ];
            }
        }
    }

    //*** Consultar los comentarios por monitoreo ***//

    public function consultMonitoringComments($id) {

        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Personalizar respuesta ---//

        foreach ($monitoring->commentByMonitoring as $datamonitoring) {

            unset($datamonitoring->pivot);
        }

        return $monitoring->commentByMonitoring;
    }

    //*** Consultar los comentarios por punto ***//

    public function consultMonitoringPointsComments($id) {

        $monitoring = CvMonitoringPoint::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El punto no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Personalizar respuesta ---//

        foreach ($monitoring->pointCommentsMonitoring as $datamonitoring) {

            unset($datamonitoring->pivot);
        }

        return $monitoring->pointCommentsMonitoring;
    }

    //*** Consultar los monitoreo por procedimiento con el id de tarea ***//

    public function monitoringByProcessTask($id) {

        //--- Informacion de la tarea ---//

        $task = CvTask::find($id);

        if (!empty($task)) {

            //--- Consultar las tareas que cuenta el procedimiento ---//
            $processWithTask = CvProcess::find($task->process[0]->id);

            //--- Consultar los monitoreos por usuario logueado ---//

            $data = array();

            $monitoring = CvMonitoring::where("state", 0)->where("process_id", $processWithTask->id)->get();

            foreach ($monitoring as $dataMonitoring) {

                if (!empty($dataMonitoring)) {

                    //--- Personalizar respuesta de monitoreo ---//
                    array_push($data, array(
                        "id" => $dataMonitoring->id,
                        "date_start" => $dataMonitoring->date_start,
                        "date_deadline" => $dataMonitoring->date_deadline,
                        "type_monitoring" => CvTypeMonitoring::find($dataMonitoring->type_monitoring_id)->name,
                    ));
                }
            }

            if (!empty($data)) {
                return $data;
            }

            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    //*** Consultar los monitoreo por procedimiento ***//

    public function monitoringByProcess($id) {

        //--- Informacion del procedimiento ---//

        $process = CvProcess::find($id);

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe",
                "response_code" => 200
            ];
        }

        if (!empty($process)) {

            //--- Consultar los monitoreos por usuario logueado ---//

            $data = array();

            if ($this->userLoggedInRol() != 3) {
                $monitoring = CvMonitoring::where("state", 0)->where("process_id", $process->id)->get();
            } else {
                $monitoring = CvMonitoring::where("state", 0)->get();
            }

            foreach ($monitoring as $dataMonitoring) {

                if (!empty($dataMonitoring)) {

                    //--- Personalizar respuesta de monitoreo ---//
                    array_push($data, array(
                        "id" => $dataMonitoring->id,
                        "date_start" => $dataMonitoring->date_start,
                        "date_deadline" => $dataMonitoring->date_deadline,
                        "type_monitoring" => CvTypeMonitoring::find($dataMonitoring->type_monitoring_id)->name,
                    ));
                }
            }

            if (!empty($data)) {
                return $data;
            } else {
                return [
                    "message" => "El usuario no cuenta con monitoreos en el actual procedimiento",
                    "response_code" => 200
                ];
            }
        }
    }

    // *** Creacion de monitoreos por procedimiento ***//

    public function monitoringProcess(Request $request) {

        //--- Informacion de la tarea ---//
        $process = CvProcess::find($request->process_id);
        $taskProcess= $process->processByTasks->where('task_sub_type_id', '>=', 4)->first();
        if (empty($taskProcess)){
            return [
                "message" => "El procedimiento no cuenta con tarea de medicion en el sistema",
                "response_code" => 500
            ];
        }

        if (count($process->processByTasks) > 0) {

            $infoProcessWithTask = $this->consultProcessWithTasks($process->processByTasks);

            if (!empty($process)) {

                //--- Consultar si la tarea de medicion de mapa cuenta con presupuesto ---//

                if ($infoProcessWithTask["task_id"] != 0) {

                    $mapGeoTask = CvTask::find($infoProcessWithTask["task_id"])->budget;

                    if (count($mapGeoTask) > 0) {

                        //--- Registrar monitoreo ---//
                        $monitoring = new CvMonitoring();

                        $monitoring->title = $request->title;
                        $monitoring->hash_map = $request->hash;
                        $monitoring->date_start = $request->date_start;
                        $monitoring->date_deadline = $request->date_deadline;
                        $monitoring->process_id = $infoProcessWithTask["process_id"];
                        $monitoring->type_monitoring_id = $request->type_monitoring_id;
                        $monitoring->user_id = $request->user_id;
                        $monitoring->user_id_creator = $this->userLoggedInId();

                        if ($monitoring->save()) {

                            //--- Guardar información de monitoreo en algolia ---//

                            $this->infoSearchMonitoring($monitoring->id);

                            //--- Registrar comentarios del monitoreo ---//
                            $comment = new CvMonitoringComment();
                            $comment->description = $request->comment;
                            $comment->monitoring_id = $monitoring->id;
                            $comment->user_id = $request->user_id;
                            $comment->save();

                            if ($comment->save()) {

                                //--- Historial del monitoreo ---//
                                $this->backFlowMonitoring($monitoring->id, $this->userLoggedInId(), $request->user_id);

                                return [
                                    "message" => "Registro exitoso",
                                    "response_code" => 200
                                ];
                            }
                        }
                    } else {

                        return [
                            "message" => "El procedimiento no cuenta aun con presupuesto",
                            "response_code" => 500
                        ];
                    }
                }
            }
        } else {
            return [
                "message" => "El procedimiento no cuenta con tarea de medicion en el sistema",
                "response_code" => 500
            ];
        }
    }

    //*** Consulta de procedimientos con tareas ***//
    public function consultProcessWithTasks($processWithTask) {

        $idTask = 0;
        $idProcess = 0;

        foreach ($processWithTask as $tasksProcess) {

            if ($tasksProcess->task_type_id == 1) {
                $idTask = $tasksProcess->id;
                $idProcess = $tasksProcess->pivot->process_id;
            }
        }

        return [
            "task_id" => $idTask,
            "process_id" => $idProcess
        ];
    }

    //*** Guardar imagenes de los puntos ***//

    public function saveImgMonitoring(Request $request) {

        $idMonitorings = array();

        //--- Obtener los archivos ---//

        $arrayFiles = $request->file('files');

        if (is_array($arrayFiles) && !empty($arrayFiles)) {

            foreach ($arrayFiles as $file) {

                $generalFileController = new GeneralFileController();

                //--- Guardar imagenes ---//

                $typeFile = strtolower(File::extension($file->getClientOriginalName()));

                $nameFile = $generalFileController->codeRandomFiles() . "_" . $file->getClientOriginalName();

                //--- Filtrar imagenes ---//

                if ($typeFile == "png" || $typeFile == "jpg" || $typeFile == "jpeg") {

                    $generalFileController->storageImage($nameFile, $file);

                    $monitoringFiles = new CvMonitoringFile();
                    $monitoringFiles->name = $nameFile;
                    $monitoringFiles->monitoring_point_id = $request->point;

                    if ($monitoringFiles->save()) {
                        array_push($idMonitorings, $monitoringFiles->id);
                    }
                }
            }
        }

        return $idMonitorings;
    }

    //*** Guardar infomración del monitoreo con puntos, comentarios e imagenes ***//

    public function saveMonitoringPointsImgAndComment(Request $request) {

        $dataMonitoring = json_decode($request->monitor_maintenance, true);

        $monitoring = CvMonitoring::find($dataMonitoring["monitor_and_maintenance_id"]);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Guardar punto ---//

        if (isset($dataMonitoring["point_id"]) && $dataMonitoring["point_id"] != null) {
            $monitoringPoint = CvMonitoringPoint::find($dataMonitoring["point_id"]);

            //--- Eliminar archivos del monitoreo para sobreescribirlos ---//
            $this->deleteFilesMonitoring($monitoringPoint->pointFilesMonitoring);
        } else {
            $monitoringPoint = new CvMonitoringPoint();
        }

        $monitoringPoint->name = $dataMonitoring["lat"] . "," . $dataMonitoring["lng"];
        $monitoringPoint->monitoring_id = $monitoring->id;

        if ($monitoringPoint->save()) {

            //--- Agregar punto al request ---//
            $request["point"] = $monitoringPoint->id;

            //--- Guardar archivos ---//
            $imgMonitoring = $this->saveImgMonitoring($request);

            //--- Guardar comentario ---//
            $monitoringComment = new CvMonitoringComment();
            $monitoringComment->description = $dataMonitoring["comment"];
            $monitoringComment->monitoring_id = $monitoring->id;
            $monitoringComment->monitoring_point_id = $monitoringPoint->id;
            $monitoringComment->user_id = $this->userLoggedInId();

            if (count($imgMonitoring) && $monitoringComment->save()) {
                return [
                    "message" => "Actualizacion exitosa",
                    "response_code" => 200
                ];
            }
        }
    }

    //*** crear historial de monitoreo ***//

    public function backFlowMonitoring($id, $from, $to) {

        //--- Consultar informacion del monitoreo ---//
        $monitoring = CvMonitoring::find($id);

        if (empty($monitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        $backByMonitoring = new CvBackupFlowMonitoring();

        $backByMonitoring->monitoring_id = $id;
        $backByMonitoring->info_monitoring = $monitoring;
        $backByMonitoring->info_monitoring_points = $monitoring->pointByMonitoring;

        if (count($monitoring->pointByMonitoring) > 0) {

            $backByMonitoring->info_monitoring_images = CvMonitoringFile::where("monitoring_point_id", $monitoring->pointByMonitoring[0]->id)->get();
        }

        if (count($monitoring->formByMonitoring) > 0) {

            $backByMonitoring->info_monitoring_form_stard = $monitoring->formByMonitoring[0]->form_stard;
            $backByMonitoring->info_monitoring_form_tracing_predial = $monitoring->formByMonitoring[0]->form_tracing_predial;
            $backByMonitoring->info_monitoring_form_certificate_maintenance_vegetable = $monitoring->formByMonitoring[0]->form_certificate_maintenance_vegetable;
            $backByMonitoring->info_monitoring_form_evaluation_provider = $monitoring->formByMonitoring[0]->form_evaluation_provider;
            $backByMonitoring->from = $from;
            $backByMonitoring->to = $to;
        }


        return $backByMonitoring->save();
    }

    //--- Eliminar archivos existentes de los monitoreo ---//
    public function deleteFilesMonitoring($files) {

        foreach ($files as $file) {
            File::delete(public_path('files/images/' . $file->name));
        }
    }

    //*** Filtrar informacion del usuario para el buscador ***//

    public function infoSearchMonitoring($monitoring_id) {

        $monitoring = CvMonitoring::find($monitoring_id);

        if (!empty($monitoring)) {

            $name = (!empty($monitoring->title)) ? $monitoring->title : "Monitoreo en proceso";
            $type = "Monitoreo";

            //--- Instancia del modelo del buscador universal con algolia ---//

            $searchAlgoliaController = new SearchAlgoliaController();

            //--- Consultar encuesta de monitoreo ---//

            $process = CvProcess::find($monitoring->process_id);

            $nameProperty = "";

            if (!empty($process)) {

                if (!empty($process->processByTasks)) {

                    foreach ($process->processByTasks as $task) {

                        $infoTask = CvTask::find($task->id);

                        if ($task->task_type_id == 3) {

                            $infoTaskProperty = $infoTask->property;

                            $property = json_decode($infoTaskProperty->info_json_general, true);

                            $nameProperty = $property["property_name"];
                        }

                        if ($task->task_type_id == 1) {

                            //--- Comparar el hash con el de presupuesto de la tarea ---//

                            $budgetTask = CvBudget::where("task_id", $task->id)->where("hash_map", $monitoring->hash_map)->first();

                            /*
                             * Buscar información de presupuesto del mapa:
                             * 1. Acciones
                             */

                            $actions = "";

                            if (!empty($budgetTask)) {

                                $actions = CvBudgetActionMaterial::find($budgetTask->action_material_id)->actionOne->name;
                            }
                        }
                    }
                }
            }

            //--- Información de ontratista ---//

            $contractor = (!empty($monitoring->userByMonitoringOne)) ? $monitoring->userByMonitoringOne->name : "";

            //--- Consultar mapa ---//

            $description = "Procedimiento" . ": " . $process->name . ", " .
                "Tipo de monitoreo" . ": " . $monitoring->typeMonitoring->name . ", " .
                "Predio" . ": " . $nameProperty . ", " .
                "Acción" . ": " . $actions . ", " .
                "Contratista" . ": " . $contractor;

            $dataSearch = [
                "name" => $name,
                "description" => $description,
                "type" => $type,
                "entity_id" => $monitoring->id
            ];


            if ($searchAlgoliaController->registerSearchUniversal($dataSearch) == 200) {
                return true;
            }
        }
    }

    public function monitoringDevolutionCreator($id_monitoring)
    {

        $monitoring=CvMonitoring::find($id_monitoring);
        if ($monitoring->user_id_creator == null){
            $validate_back=CvBackupFlowMonitoring::where('monitoring_id',$monitoring->id)->where('from','!=',null);
            if ($validate_back->exists()){
                $monitoring->user_id=$validate_back->from;
            }
        }else{
            $monitoring->user_id=$monitoring->user_id_creator;
        }
        $monitoring->save();
        return[
            'message'=>'Monitoreo entregado',
            'code'=>200
        ];
    }


}
