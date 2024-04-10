<?php

namespace App\Http\Controllers;

use App\CvProcess;
use App\CvProcessByProjectActivity;
use App\CvProcessTypePsa;
use App\CvUnionOfProcess;
use App\Http\Requests\ProcessRequest;
use App\User;
use App\CvTask;
use App\CvProject;
use App\CvProjectActivity;
use App\CvSubTypeTask;
use Carbon\Carbon;
use App\CvProperty;
use App\CvRole;
use App\CvBackupFlowTasks;
use App\CvFile;
use File;
use App\Http\Controllers\General\GeneralTaskController;
use App\Http\Controllers\General\GeneralMonitoringController;
use App\Http\Controllers\Method\FunctionsSpecificController;
use App\Http\Controllers\General\GeneralQuotesSaveController;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Integer;
use App\CvPotentialProperty;

class ProcessController extends Controller
{

    // *** Consultar procedimientos en general o especificos de acuerdo al rol del usuario autenticado *** //

    public function index()
    {

        $allProcess = CvProcess::orderBy('id', 'desc')->get();

        switch ($this->userLoggedInRol()) {

            case 1:
            case 3:
                $listsProcess = $this->getProceesByUserLogin();
                break;
            case 9:
                $listsProcess = $this->getProcessByCoordination($allProcess, [9, 15]);
                break;
            case 10:
                $listsProcess = $this->getProcessByCoordination($allProcess, [10, 16]);
                break;
            case 15:
            case 16:
                $listsProcess = $this->getProceesByUserLogin();
                break;
            case 12:
                $listsProcess = $allProcess;
                break;
            case 13:
                $listsProcess = $this->getProcessByCoordination($allProcess, [13, 17]);
                break;
            default:
                $listsProcess = $this->getProceesByUserLogin();
                break;
        }

        /*
         * Consulta personalizada para cada procedimiento para consultar el total de los subtipos y el subtipo actual del procedimiento
         */

        $processArray = array();

        if (!empty($listsProcess)) {
            foreach ($listsProcess as $process) {

                array_push($processArray, array(
                    $process->id => []
                ));
            }
        }

        /*
         * Guardar los subtipos de las tareas por cada procedimiento
         */

        if (count($processArray) > 0) {
            foreach ($processArray as $index => $idProcess) {
                foreach ($idProcess as $item => $value) {
                    foreach ($listsProcess as $process) {
                        foreach ($process->processByTasks as $task) {

                            if ($item == $task->pivot->process_id) {
                                array_push($processArray[$index][$item], CvSubTypeTask::find($task->task_sub_type_id)->order);
                            }
                        }
                    }
                }
            }
        }

        /*
         * Obtener el valor maximo de cada subtipo y eliminar los procedimientos que no cuenten con tareas
         */

        $processTasksSubTypes = array();

        if (count($processArray) > 0) {
            foreach ($processArray as $index => $processWithSubType) {
                foreach ($processWithSubType as $item => $subType) {
                    if (!empty($processArray[$index][$item])) {
                        $processArray[$index][$item] = max($processArray[$index][$item]);
                        array_push($processTasksSubTypes, $processArray[$index]);
                    } else {
                        array_push($processTasksSubTypes, $processArray[$index]);
                    }
                }
            }
        }

        /*
         * Optimizar respuesta de procedimientos
         */

        $personalizedListsProcess = array();

        //--- Obtener el ultimo sub tipo ---//
        $lastSubtype = CvSubTypeTask::all()->last();

        $date = Carbon::now();

        if (count($listsProcess) > 0) {
            foreach ($listsProcess as $listProcess) {
                foreach ($processTasksSubTypes as $processData) {
                    foreach ($processData as $index => $valueSubType) {

                        if ($listProcess->id == $index) {

                            /*
                             * Calcular el porcentaje del orden del sub tipo de la tarea
                             */

                            $valueSubTypeInfo = (!empty($valueSubType)) ? $valueSubType : 1;

                            $percentage = (((int)$valueSubTypeInfo / (int)$lastSubtype->id) * 100);

                            /*
                             * agregar el tipo de procedimiento.
                             * */

                            if ($listProcess->type_process) {
                                $typeProcess = $listProcess->type_process;
                            } else if (CvProcessTypePsa::where('proccess_id', $listProcess->id)->first()) {
                                $typeProcess = 'psa';
                            } else {
                                $typeProcess = '';
                            }

                            array_push($personalizedListsProcess, array(
                                "id" => $listProcess->id,
                                "name" => $listProcess->name,
                                "description" => $listProcess->description,
                                "created_at" => $date->format($listProcess->created_at),
                                "updated_at" => $date->format($listProcess->updated_at),
                                "sub_type_step" => round($percentage, 2),
                                "sub_type_total" => 100,
                                "type_process" => $typeProcess,
                                "hasPotentialProperty" => $listProcess->potential_property_id
                            ));
                        }
                    }
                }
            }
        }

        return $personalizedListsProcess;
    }

    private function getProceesByUserLogin() {

        //--- Si el usuario no cuenta con alguna tarea no podra visualizar los procesos ---//
        $allprocs = array();
        $user_tasks = User::find($this->userLoggedInId())->task;
        $user_task_open = User::find($this->userLoggedInId())->usertaskOpen;

        foreach ($user_tasks as $task) {
            foreach (CvTask::find($task->id)->process as $detailpros) {
                array_push($allprocs, $detailpros);
            }
        }
        foreach ($user_task_open as $taskopen) {
            $insert = 0;
            foreach ($allprocs as $validatearray) {
                if ($validatearray['id'] == $taskopen->process_id) {
                    $insert = 1;
                }
            }
            if ($insert == 0) {
                array_push($allprocs, $taskopen->process);
            }
        }

        return collect($allprocs);
    }

    private function getProcessByCoordination($collProcess, $filterRolIds)
    {
        $response = new Collection();
        foreach ($collProcess as $process) {

            foreach ($process->processByProjectByActivity as $processByActivity) {
                
                if ($processByActivity->bycoordination) {
                    $rol = $processByActivity->bycoordination->roleadd;
                    foreach ($filterRolIds as $filterRolId) {

                        if ($rol->id == $filterRolId) {
                            $response->push($process);
                        }
                    }
                }
            }
        }
        return $response;
    }

    //*** Ruta por defecto de laravel - retorna error 404 ***//
    public function create()
    {
        abort(404);
    }

    //*** Registrar procedimientos ***//
    public function store(ProcessRequest $request)
    {

        $campos = $request->all();
        //--- Saber si se guardaron todos los registros de las actividades por procedimiento ---//
        $processTotal = [];

        // si potential_property_id es null, este una proceso abierto o sin predio.
        if (!empty($campos['potential_property_id'])) {
            $functionSpecificController = new FunctionsSpecificController();

            $infoPotentialProperty = $functionSpecificController->infoPotentialProperty($request->potential_property_id);

            if (is_array($infoPotentialProperty)) {
                return $infoPotentialProperty;
            }

            //--- Instacia de procedimientos ---//
            $process = new CvProcess();
            $process->name = $request->name;
            $process->description = $request->description;
            $process->potential_property_id = $request->potential_property_id;
            if (array_key_exists('type_process', $campos)) {
                $process->type_process = $campos['type_process'];
            }

            if ($process->save()) {

                //--- Replicar la informacion del predio potencial al procedimiento ---//
                $functionSpecificController->replicateInfoOfDocumentsProcess($process->id, $infoPotentialProperty);

                //--- Cambiar el estado a la cuota de un predio potencial ---//
                if (!empty($infoPotentialProperty)) {
                    $generalQuotes = new GeneralQuotesSaveController();
                    $generalQuotes->activePropertyPotential($infoPotentialProperty);
                }

                //--- Instancia de procedimientos con actividades ---//
                foreach ($request->activities as $projectActivies) {

                    $projectByActivies = new CvProcessByProjectActivity();

                    $projectByActivies->project_activity_id = $projectActivies;
                    $projectByActivies->process_id = $process->id;

                    $projectByActivies->save();

                    //--- Guardar un valor en cada registro ---//
                    array_push($processTotal, 1);
                }
            }
        } else {

            //--- Instacia de procedimientos ---//
            $process = new CvProcess();
            $process->name = $request->name;
            $process->description = $request->description;
            $process->type_process = $request->type_process;

            if ($process->save()) {
                //--- Instancia de procedimientos con actividades ---//
                foreach ($request->activities as $projectActivies) {

                    $projectByActivies = new CvProcessByProjectActivity();

                    $projectByActivies->project_activity_id = $projectActivies;
                    $projectByActivies->process_id = $process->id;

                    $projectByActivies->save();

                    //--- Guardar un valor en cada registro ---//
                    array_push($processTotal, 1);
                }
            }
        }

        if ($request->nest_procedure == TRUE) {

            $father = CvProcess::find($request->parent_procedure);

            $unionProces = new CvUnionOfProcess();
            $unionProces->process_father_id = $father->id;
            $unionProces->process_son_id = $process->id;
            $unionProces->save();
        }
        //--- Mensaje para verificar que el registro fue efectivo ---//
        if (!empty($processTotal)) {

            return [
                "message" => "Registro exitoso",
                "response_code" => 200,
                "object_id" => $process->id
            ];
        }
    }

    // *** Información del procedimiento *** //
    public function show($id)
    {
        $info = array();
        $projects_all = array();

        $process = CvProcess::find($id);
        $process->processByProjectByActivity;

        // --- Obtener el proyecto en el que se encuentra vinculadas las actividades --- //

        foreach ($process->processByProjectByActivity as $projectActivities) {
            $activideid = $projectActivities->projectByActivity;
            array_push($projects_all, $activideid);
            foreach ($activideid as $projec) {
                $projec->typeProgram;
            }
        }

        array_push($info, array("process_with_activities" => $process, "activities_with_programs" => array_collapse($projects_all)));
        return $info;
    }

    // *** Consultar información relevante para actualizar un procedimiento *** //
    public function edit($id)
    {

        $process = CvProcess::find($id);

        if (!empty($process)) {
            $process->processByProjectByActivity;

            //--- Consultar las actividades que contiene el procedimiento ---//

            $activities = array();
            $projects = array();
            $programs = array();

            foreach ($process->processByProjectByActivity as $projectActivity) {

                $project = CvProjectActivity::find($projectActivity->pivot->project_activity_id);

                array_push($activities, $projectActivity->pivot->project_activity_id);

                if (!empty($project)) {

                    //--- Consultar el proyecto y programa relacionado a la actividad ---//

                    array_push($projects, $project->projectByActivity[0]->id);

                    $program = CvProject::find($project->projectByActivity[0]->id);

                    array_push($programs, $program->programByProject[0]->id);
                }
            }


            //--- Respuesta personzalida ---//

            $info = array();

            $projectsUnique = array_values(array_unique($projects));
            $programsUnique = array_values(array_unique($programs));

            array_push($info, array(
                "id" => $process->id,
                "name" => $process->name,
                "description" => $process->description,
                "activities" => $activities,
                "project" => $projectsUnique,
                "program" => $programsUnique,
                "type_process" => $process->type_process,
                "hasPotentialProperty" => $process->type_process,
            ));
            return count($info) > 0 ? $info[0] : null;
        } else {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    // *** Actualizar procedimientos *** //

    public function update(ProcessRequest $request, $id)
    {

        //--- Instacia de procedimientos ---//
        $process = CvProcess::find($id);
        $process->name = $request->name;
        $process->description = $request->description;

        //--- Mensaje para verificar que el registro fue efectivo ---//
        if ($process->save()) {
            return [
                "message" => "Registro actualizado",
                "response_code" => 200
            ];
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function destroy($id)
    {
        abort(404);
    }

    // *** Consultar las tareas por procedimiento y monitoreos*** //

    public function consultTaskByProcessByMonitoring($id)
    {

        $processController = new GeneralTaskController();
        $monitoringController = new GeneralMonitoringController();

        return [
            "task" => (isset($processController->consultTaskByProcess($id)["message"])) ? [] : $processController->consultTaskByProcess($id),
            "monitoring" => (isset($monitoringController->monitoringByProcess($id)["message"])) ? [] : $monitoringController->monitoringByProcess($id)
        ];
    }

    // *** Consultar las tareas por procedimiento con informacion del predio *** //

    public function consultProcessWithTaskProperty($id)
    {

        $processController = new GeneralTaskController();

        $processByTask = [];

        if (isset($processController->consultTaskByProcess($id)["message"])) {
            $processByTask = [];
        } else {
            $processByTask = $processController->consultTaskByProcess($id);
        }

        $property_name = null;
        $property_id = null;

        $infoProcess = CvProcess::find($id);
        if ($infoProcess->potential_property_id != null) {
            $property_name = CvPotentialProperty::find($infoProcess->potential_property_id)->property_name;
            $property_id = $infoProcess->potential_property_id;
        }

        if (count($processByTask) > 0) {

            //--- Obtener el nombre del predio ---//
            foreach ($processByTask as $task) {

                $property_name = CvPotentialProperty::find($task->process->first()->potential_property_id)->property_name;
                $property_id = $task->process->first()->potential_property_id;

                if ($task->task_type_id == 3) {

                    $property = CvProperty::find($task->property_id);

                    if (!empty($property)) {
                        $dataPRoperty = json_decode($property->info_json_general, true);
                        $property_name = $property->taskOne->process->first()->potentialProperty->name;
                        $property_id = $property->taskOne->process->first()->potentialProperty->id;
                    }
                }
            }
        }

        //--- Respuesta personalizada ---//

        $consultTaskByProcessWithProperty = $processController->consultTaskByProcess($id);
        $count = count($consultTaskByProcessWithProperty);
        foreach ($consultTaskByProcessWithProperty as $value) {

            if (isset($value->taskUser)) {
                if (!empty($value->taskUser[0])) {
                    $user = User::find($value->taskUser[0]->user_id);
                    $value["user_name"] = $user->names . " " . $user->last_names;
                    $value["user_role"] = $user->role->name;
                }
            }
        }

        $processController->consultTaskByProcess($id);
        $all_task = (isset($processController->consultTaskByProcess($id)["message"])) ? [] : $consultTaskByProcessWithProperty;

        return [
            "task" => $all_task,
            "property" => $property_name,
            "propertyId" => $property_id,
            "count_task" => $count
        ];
    }

    //*** Listar los usuarios que intervinieron en un procedimiento ***//

    public function listsUsersInterventionProcess($id)
    {

        $tasksArrayId = [];

        //--- Consultar las tareas del procedimiento ---//

        $process = CvProcess::find($id);

        if (empty($process)) {
            return [];
        }

        if (isset($process->processByTasks) && !empty($process->processByTasks)) {

            foreach ($process->processByTasks as $processid) {

                array_push($tasksArrayId, $processid->id);
            }
        }

        $usersIntervention = [];

        if (count($tasksArrayId) > 0) {

            foreach ($tasksArrayId as $taskId) {

                $backupFlowTasks = CvBackupFlowTasks::where("info_task_id", $taskId)->get();

                foreach ($backupFlowTasks as $backupTask) {
                    $userFrom = User::find($backupTask->info_user_from);
                    $userTo = User::find($backupTask->info_user_to);

                    array_push($usersIntervention, $userFrom, $userTo);
                }
            }

            // --- Personalizar respuesta --- //

            $responsePerzonalizedUser = [];

            if (count($usersIntervention) > 0) {

                foreach (array_unique($usersIntervention) as $user) {

                    array_push($responsePerzonalizedUser, array(
                            "id" => $user->id,
                            "names" => $user->names,
                            "last_names" => $user->last_names,
                            "name_with_last_names" => $user->names . " " . $user->last_names,
                            "role_id" => $user->role_id,
                            "role_name" => CvRole::find($user->role_id)->name
                        )
                    );
                }
                return $responsePerzonalizedUser;
            }
        }

        //--- El procedimiento no cuenta con tareas en el sistema ---//

        return [];
    }

    //*** Consulta las actividades de un procedimiento ***//

    public function consultActivitiesByProcess($id)
    {

        $processByActivities = CvProcess::find($id);

        if (empty($processByActivities)) {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "code" => 500
            ];
        }

        $info = array();

        if (isset($processByActivities->processByProjectByActivity)) {
            if (!empty($processByActivities->processByProjectByActivity)) {

                foreach ($processByActivities->processByProjectByActivity as $data) {

                    //--- Personalizar respuesta ---//

                    array_push($info, array(
                        "id" => $data->id,
                        "name" => $data->name
                    ));
                }
            }
        }
        return $info;
    }

    //*** Consultar los archivos de un predio potencial vinculado a un procedimiento ***//
    public function consultFilesProcessWithPotentialProperty($process_id)
    {

        $process = CvProcess::find($process_id);

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "code" => 500
            ];
        }

        $info = array();

        //--- Consultar la tarea de encuesta ---//
        if (!empty($process->processByTasks)) {
            foreach ($process->processByTasks as $taskWithProcess) {
                if ($taskWithProcess->task_type_id == 3) {

                    $ccFiles = array();
                    $ctFiles = array();

                    foreach ($taskWithProcess->taskFilePivot as $file) {

                        if ($file->task_sub_type_id == 2) {

                            $type = "doc";
                            $fileCC = CvFile::find($file->file_id);

                            if (strtolower(File::extension($fileCC->name)) == "png" || strtolower(File::extension($fileCC->name)) == "jpg" || strtolower(File::extension($fileCC->name)) == "jpeg") {
                                $type = "img";
                            }
                            $fileCC["type"] = $type;
                            array_push($ccFiles, $fileCC);
                        }

                        if ($file->task_sub_type_id == 8) {

                            $type = "doc";
                            $fileCT = CvFile::find($file->file_id);

                            if (strtolower(File::extension($fileCT->name)) == "png" || strtolower(File::extension($fileCT->name)) == "jpg" || strtolower(File::extension($fileCT->name)) == "jpeg") {
                                $type = "img";
                            }

                            $fileCT["type"] = $type;
                            array_push($ctFiles, $fileCT);
                        }
                    }

                    //--- Consultar los 4 archivos ---//
                    $info = [
                        "potential_id" => $process->potential_property_id,
                        "poll" => $taskWithProcess->property,
                        "letter" => $process->letterIntention,
                        "cc" => $ccFiles,
                        "ct" => $ctFiles
                    ];
                }
            }
            return $info;
        }
    }

    //*** Conculta procedimiento con detalle de padre e hijos ***//
    public function processDetail($id)
    {
        $info = array();
        $activitiearray = array();
        $program = array();
        $project = array();
        $process = CvProcess::find($id);

        $arrSteps = Array();
        foreach ($process->processByTasks as $taks) {
            array_push($arrSteps, $taks->taskSubType->order);
        }
        $lastSubtype = CvSubTypeTask::all()->last();

        if (count($arrSteps) > 0) {
            $valueSubTypeInfo = max($arrSteps);
        } else {
            $valueSubTypeInfo = 1;
        }

        $percentage = (((int)$valueSubTypeInfo / (int)$lastSubtype->id) * 100);

        $info['nest_procedure'] = $process->processSons;
        $info['parent_procedure'] = [];
        $union = CvUnionOfProcess::where('process_son_id', $id);
        if ($union->exists()) {
            $info['parent_procedure'] = $process->processFhater[0];
        }
        $activities = $process->processByProjectByActivity;

        foreach ($activities as $activite) {
            $project['id'] = $activite->projectByActivity[0]['id'];
            $project['name'] = $activite->projectByActivity[0]['name'];
            $project['description'] = $activite->projectByActivity[0]['description'];
            $project['state'] = $activite->projectByActivity[0]['state'];

            $program['id'] = $activite->projectByActivity[0]->programByProject[0]['id'];
            $program ['name'] = $activite->projectByActivity[0]->programByProject[0]['name'];

            array_push($activitiearray, array(
                'id' => $activite->id,
                'name' => $activite->name,
                'project' => $project,
                'program' => $program
            ));
        }
        $info['activities'] = [$activitiearray][0];

        //LLenar info
        $info['id'] = $process->id;
        $info['name'] = $process->name;
        $info['description'] = $process->description;
        $info['type_process'] = $process->type_process;
        $info['potential_property_id'] = $process->potential_property_id;
        $info['subTypeStep'] = $percentage;
        $info['budgetAssigned'] = 'valor';
        $info['budgetExecuted'] = 'valor';

        return $info;
    }

    public function taskMeasurement()
    {
        $info = array();
        $process = CvProcess::all();
        foreach ($process as $proce) {
            $task = $proce->processByTasks->where('task_sub_type_id', '>=', 4)->first();
            if (!empty($task)) {
                array_push($info, array(
                    "name" => $proce->name,
                    "id" => $proce->id
                ));
            }
        }
        return $info;
    }

    public function superAdminProcess()
    {


        $listsProcess = CvProcess::orderBy('id', 'desc')->get();

        /*
         * Consulta personalizada para cada procedimiento para consultar el total de los subtipos y el subtipo actual del procedimiento
         */

        $processArray = array();

        if (!empty($listsProcess)) {
            foreach ($listsProcess as $process) {
                if ($process->processByTasks->where('task_sub_type_id', '>', 32)->first()) {
                    array_push($processArray, array(
                        $process->id => []
                    ));
                }
            }
        }

        /*
         * Guardar los subtipos de las tareas por cada procedimiento
         */

        if (count($processArray) > 0) {
            foreach ($processArray as $index => $idProcess) {
                foreach ($idProcess as $item => $value) {
                    foreach ($listsProcess as $process) {
                        foreach ($process->processByTasks as $task) {
                            if ($task->task_sub_type_id > 32) {
                                if ($item == $task->pivot->process_id) {
                                    array_push($processArray[$index][$item], CvSubTypeTask::find($task->task_sub_type_id)->order);
                                }
                            }
                        }
                    }
                }
            }
        }

        /*
         * Obtener el valor maximo de cada subtipo y eliminar los procedimientos que no cuenten con tareas
         */

        $processTasksSubTypes = array();

        if (count($processArray) > 0) {
            foreach ($processArray as $index => $processWithSubType) {
                foreach ($processWithSubType as $item => $subType) {
                    if (!empty($processArray[$index][$item])) {
                        $processArray[$index][$item] = max($processArray[$index][$item]);
                        array_push($processTasksSubTypes, $processArray[$index]);
                    } else {
                        array_push($processTasksSubTypes, $processArray[$index]);
                    }
                }
            }
        }

        /*
         * Optimizar respuesta de procedimientos
         */

        $personalizedListsProcess = array();

        //--- Obtener el ultimo sub tipo ---//
        $lastSubtype = CvSubTypeTask::all()->last();

        $date = Carbon::now();

        if (count($listsProcess) > 0) {
            foreach ($listsProcess as $listProcess) {
                foreach ($processTasksSubTypes as $processData) {
                    foreach ($processData as $index => $valueSubType) {

                        if ($listProcess->id == $index) {

                            /*
                             * Calcular el porcentaje del orden del sub tipo de la tarea
                             */

                            $valueSubTypeInfo = (!empty($valueSubType)) ? $valueSubType : 1;

                            $percentage = (((int)$valueSubTypeInfo / (int)$lastSubtype->id) * 100);

                            /*
                             * agregar el tipo de procedimiento.
                             * */

                            if ($listProcess->type_process) {
                                $typeProcess = $listProcess->type_process;
                            } else if (CvProcessTypePsa::where('proccess_id', $listProcess->id)->first()) {
                                $typeProcess = 'psa';
                            } else {
                                $typeProcess = '';
                            }

                            array_push($personalizedListsProcess, array(
                                "id" => $listProcess->id,
                                "name" => $listProcess->name,
                                "description" => $listProcess->description,
                                "created_at" => $date->format($listProcess->created_at),
                                "updated_at" => $date->format($listProcess->updated_at),
                                "sub_type_step" => round($percentage, 2),
                                "sub_type_total" => 100,
                                "type_process" => $typeProcess,
                                "hasPotentialProperty" => $listProcess->potential_property_id
                            ));
                        }
                    }
                }
            }
        }

        return $personalizedListsProcess;
    }

}
