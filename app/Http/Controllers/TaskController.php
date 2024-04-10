<?php

namespace App\Http\Controllers;


use App\CvAllUserTasksMeasurement;
use App\CvAssociatedContribution;
use App\CvBackupTaskExecution;
use App\CvBackupTaskOpenAndEspecial;
use App\CvContributionSpecies;
use App\CvProcessTypePsa;
use App\CvTaskOpenBudget;
use App\CvTaskOpenBudgetSpecies;
use App\CvTaskOpenSubType;
use App\CvTaskOpenUser;
use App\CvPotentialProperty;
use App\Http\Controllers\General\GeneralPsaController;
use Exception;
use http\Env\Response;
use Illuminate\Http\Request;
use App\CvTask;
use App\CvTaskUser;
use App\CvTaskProcess;
use App\User;
use App\CvProject;
use App\CvProcess;
use App\Http\Requests\TaskRequest;
use App\CvComment;
use App\CvCommentByTask;
use App\CvSubTypeTask;
use App\CvTaskType;
use App\CvTaskOpen;
use App\CvProjectActivity;
use App\Http\Controllers\General\GeneralTaskController;
use App\Http\Controllers\General\GeneralHistoryTaskController;
use App\Http\Controllers\General\GeneralSubTypeTaskController;
use App\Http\Controllers\General\GeneralOneSignalController;
use App\Http\Controllers\General\GeneralNotificationController;
use App\Http\Controllers\General\GeneralLetterIntentionController;
use App\Http\Controllers\General\GeneralEmailController;
use phpDocumentor\Reflection\DocBlock\Tag\DeprecatedTag;
use Sami\Parser\Filter\TrueFilter;

class TaskController extends Controller {

    // *** Consultar tareas en general o especificas de acuerdo al rol del usuario autenticado *** //

    public function index() {

        // --- Instancia clase para obtener las tareas del usuario logueado o por rol --- //

        $generalTaskController = new GeneralTaskController();

        return $generalTaskController->listTasksByRol();
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function create() {

        abort(404);
    }

    // *** Registro de una nueva tarea *** //

    public function store(TaskRequest $request) {

        //--- Registrar tarea abierta ---//
        if ($request->open == TRUE) {

            return $this->registerOrUpdateTaskOpen($request, null);
        }

        //--- Validar si existe el tipo de tarea ---//

        $typeTask = CvTaskType::find($request->type_id);

        if ($request->type_id != 0) {
            if (empty($typeTask)) {
                return [
                    "message" => "El tipo de tarea no existe en el sistema",
                    "code" => 200
                ];
            }
        }

        //--- Validar si el procedimiento existe en el sistema ---//
        $existProcess = CvProcess::find($request->proccess_id);

        if (empty($existProcess)) {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "code" => 500
            ];
        }
        foreach ( $existProcess->processByTasks as $task){
            if ($task->task_sub_type_id >= 4){
                return [
                    "message" => "Ya existe una tarea de medicion para el procedimiento",
                    "code" => 500
                ];
            }
        }

        //--- Registrar carta de intencion ---//
        if ($request->type_id == 5) {

            $generalLetterIntentionController = new GeneralLetterIntentionController();
            return $generalLetterIntentionController->registerLetterIntention($request);
        }

        //--- Registrar nuevo registro de tarea ---//
        $newTask = new CvTask();

        $newTask->description = $request->description;
        $newTask->task_type_id = $request->type_id;
        $newTask->task_status_id = 1;
        $newTask->option_date = $request->option_date;
        $newTask->date_start = $request->startdate;
        $newTask->date_end = $request->deadline;
        //Sin tipo de tarea sera MEDICION por defecto
        if ($newTask->task_type_id == 0){
            $newTask->task_type_id=1;
        }
        //--- Ingresar el tipo de tarea de acuerdo al usuario ---//

        $subTypeController = new GeneralSubTypeTaskController();

        //--- Cuando sub tipo no exista se envia como cero el parametro ---//
        if ($newTask->task_sub_type_id == "") {
            $newTask->task_sub_type_id = 0;
        }

        $newTask->task_sub_type_id = $subTypeController->subTypeTask($this->userLoggedInRol(), $newTask->task_type_id, $newTask->task_sub_type_id);

        // --- Relacionar predio potencial a la tarea registrada --- //
        if ($request->property != "" && $request->property != null) {

            $newTask->property_id = $request->property;
        }
        //Medicio por defecto si Sub tipo no se define
        if ($newTask->task_sub_type_id == null) {
            $newTask->task_sub_type_id = 4;
        }
        if ($newTask->save()) {

            //--- Guardar registro con relacion a tarea por usuarios ---//
            if (empty($request->user_id)) {
                return [
                    "message" => "No se han seleccionado usuarios con rol de guarda cuenca",
                    "code" => 500
                ];
            }

            foreach ($request->user_id as $valueUserId) {

                if($valueUserId != null) {
                    $taskByUser = new CvTaskUser();
                    $taskByUser->user_id = $valueUserId;
                    $taskByUser->task_id = $newTask->id;
                    $taskByUser->save();
                }
            }

            $taskByProject = new CvTaskProcess();

            $taskByProject->task_id = $newTask->id;
            $taskByProject->process_id = $request->proccess_id;

            //--- Registrar comentarios ---//
            if ($request->comments != "") {

                $comment = new CvComment;
                $comment->description = $request->comments;
                $comment->save();

                //--- Relacionar comentario con la tarea ---//

                $taskByComment = new CvCommentByTask();
                $taskByComment->comment_id = $comment->id;
                $taskByComment->task_id = $newTask->id;
                $taskByComment->user_id = $this->userLoggedInId();
                $taskByComment->task_sub_type_id = $newTask->task_sub_type_id;
                $taskByComment->save();
            }


            if ($taskByProject->save()) {

                //--- Obtener la información de la tarea registrada ---//
                $infoTask = $this->show($newTask->id);

                //--- Enviar información de la tarea para registrar su historial ---//
                $historyTask = array();

                array_push($historyTask, array(
                        "type_task" => "Register_task",
                        "info" => $infoTask,
                        "status" => $newTask->task_status_id,
                        "sub_type" => $newTask->task_sub_type_id,
                        "task_id" => $newTask->id,
                        "user_from" => $this->userLoggedInId(),
                        "user_to" => implode(",", $request->user_id)
                    )
                );

                //--- Enviar información al controlador en el cual va a filtrar los datos de la tarea ---//
                $historyController = new GeneralHistoryTaskController();

                if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                    //--- Notificacion ---//
                    $this->notificationCreateUpdateTask($request, $newTask);

                    return [
                        "message" => "Registro exitoso",
                        "response_code" => 200,
                        "object_id" => $newTask->id,
                        "sub_type_id" => $newTask->task_sub_type_id,
                        "sub_type_name" => CvSubTypeTask::find($newTask->task_sub_type_id)->name,
                        "open" => false
                    ];
                }
            }
        }
    }

    // *** Consultar toda la información de la tarea *** //
    public function show($id) {

        $info = array();

        $task = CvTask::find($id);

        if (!empty($task)) {

            foreach ($task->process as $process) {

                $process = CvProcess::find($process->id);

                /*
                 * Consultar actividades del procedimiento
                 */
                $activitiesProcess = array();
                $projectsActivity = array();
                $projectsProgram = array();

                //--- Respuesta personalizada de las actividades del procedimiento ---//
                foreach ($process->processByProjectByActivity as $valueProcessByActivities) {

                    if (!empty($valueProcessByActivities)) {
                        array_push($activitiesProcess, array(
                            "id" => $valueProcessByActivities->id,
                            "name" => $valueProcessByActivities->name
                        ));
                    }
                }

                /*
                 * Consultar proyectos por actividad
                 */
                foreach ($process->processByProjectByActivity as $valueProjectByActivity) {

                    if (isset($valueProjectByActivity->projectByActivity[0])) {

                        if (!empty($valueProjectByActivity->projectByActivity)) {
                            array_push($projectsActivity, $valueProjectByActivity->projectByActivity[0]);
                        }
                    }
                }

                //--- Obtener los proyectos sin estar duplicados ---//
                $idsActivities = array_column($projectsActivity, 'id');
                $idsActivities = array_values(array_unique($idsActivities));

                $arrayProjectActivity = array_filter($projectsActivity, function ($key, $value) use ($idsActivities) {
                    return in_array($value, array_keys($idsActivities));
                }, ARRAY_FILTER_USE_BOTH);

                /*
                 * Consultar programas de los proyectos correspondiente
                 */
                foreach ($arrayProjectActivity as $projectByProgram) {

                    if (isset($projectByProgram->programByProject[0])) {

                        if (!empty($projectByProgram->programByProject)) {
                            array_push($projectsProgram, $projectByProgram->programByProject[0]);
                        }
                    }
                }


                //--- Obtener los proyectos sin estar duplicados ---//
                $idsPrograms = array_column($projectsProgram, 'id');
                $idsPrograms = array_values(array_unique($idsPrograms));

                $arrayProjectPrograms = array_filter($projectsProgram, function ($key, $value) use ($idsPrograms) {
                    return in_array($value, array_keys($idsPrograms));
                }, ARRAY_FILTER_USE_BOTH);
            }

            if (!empty($arrayProjectPrograms)) {

                //--- Agregar nuevas propiedades de usuario ---//
                $roleUser = array();

                foreach ($task->user as $user) {

                    //--- Usuarios --- //
                    $dataUser = User::find($user->id)->role;
                    array_push($roleUser, $dataUser);
                }

                if (!empty($task->user[0])){
                    $array_user=array(
                        "id" => $task->user[0]->id,
                        "names" => $task->user[0]->names,
                        "last_names" => $task->user[0]->last_names,
                        "name" => $task->user[0]->name,
                        "email" => $task->user[0]->email
                    );

                }else{
                    $array_user=null;
                }

                if (!empty($roleUser[0])){
                    $arra_role= array(
                        "id" => $roleUser[0]->id,
                        "name" => $roleUser[0]->name,
                    );

                }else{

                    $arra_role=null;
                }

                //--- Respuesta personalizada ---//
                array_push($info, array(
                    "id" => $task->id,
                    "description" => $task->description,
                    "option_date" => $task->option_date,
                    "startdate" => $task->date_start,
                    "deadline" => $task->date_end,
                    "comments" => $task->comment,
                    "status" => array(
                        "id" => $task->taskStatus->id,
                        "name" => $task->taskStatus->name
                    ),
                    "type" => array(
                        "id" => $task->taskType->id,
                        "name" => $task->taskType->name
                    ),
                    "sub_type" => array(
                        "id" => $task->taskSubType->id,
                        "name" => $task->taskSubType->name
                    ),
                    "process" => array(
                        "id" => $task->process[0]->id,
                        "name" => $task->process[0]->name,
                        "description" => $task->process[0]->description,
                    ),
                    "potential_detail" => array(
                        "id" => $task->process[0]->potentialProperty->id,
                        "name" => $task->process[0]->potentialProperty->property_name,
                    ),
                    "activity" => $activitiesProcess,
                    "project" => $arrayProjectActivity,
                    "program" => $arrayProjectPrograms,
                    "user" => $array_user,
                    "role" =>$arra_role,
                ));
                return $info[0];
            }
        } else {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    //*** Mostrar información de una tarea en especifico ***//

    public function edit($id) {

        //--- Consultar tarea a editar ---//
        $updateTask = CvTask::find($id);

        if (!empty($updateTask)) {

            //--- Obtener información relevante de usuario ---//
            $infoUser = array();

            //--- ID de los usuarios vinculado a una tarea ---//
            $idUsers = array();

            foreach ($updateTask->user as $user) {
                $infoUser["user_id"] = $user->id;
                array_push($idUsers, $user->id);
            }

            foreach ($updateTask->process as $process) {
                $infoUser["process_id"] = $process->id;
            }

            //--- Rol del usuario ---//
            $role = User::find($infoUser["user_id"])->role;

            //--- Enviar información para la edición de la tarea ---//
            $info = array(
                "id" => $updateTask->id,
                "description" => $updateTask->description,
                "date_start" => $updateTask->date_start,
                "date_end" => $updateTask->date_end,
                "option_date" => $updateTask->option_date,
                "state" => $updateTask->state,
                "type_id" => $updateTask->task_type_id,
                "type_name" => $updateTask->taskType->name,
                "status_id" => $updateTask->task_status_id,
                "comments" => $updateTask->comment,
                "user" => $idUsers,
                "sub_type" => CvSubTypeTask::find($updateTask->task_sub_type_id),
                "process" => $infoUser["process_id"],
                "role" => $role->id
            );

            return $info;
        }
        return [
            "message" => "La tarea no existe en el sistema",
            "response_code" => 200
        ];
    }

    //*** Actualizar información de una tarea en especifico ***//

    public function update(TaskRequest $request, $id) {

        //--- Actualizar información de tarea ---//
        $updateTask = CvTask::find($id);

        if (!empty($updateTask)) {

            $updateTask->description = $request->description;
            $updateTask->option_date = $request->option_date;
            $updateTask->date_start = $request->startdate;
            $updateTask->date_end = $request->deadline;

            if ($updateTask->save()) {

                //--- Notificacion ---//
                $this->notificationCreateUpdateTask($request, $updateTask);

                $taskByUser = CvTaskUser::where("task_id", $id)->get();

                foreach ($taskByUser as $valueTaskByUser) {
                    $valueTaskByUser->delete();
                }

                //--- Actualizar la información de multiples usuarios a una tarea ---//
                foreach ($request->user_id as $usersId) {
                    $taskByUser = CvTaskUser::where("task_id", $id)->where("user_id", $usersId)->exists();
                    if ($taskByUser == false) {
                        $newTaskByUser = new CvTaskUser();
                        $newTaskByUser->user_id = $usersId;
                        $newTaskByUser->task_id = $updateTask->id;
                        $newTaskByUser->save();

                        if ($updateTask->task_sub_type_id == 4){
                            $all_user=new CvAllUserTasksMeasurement();
                            $all_user->send=0;
                            $all_user->user_id=$usersId;
                            $all_user->task_id=$updateTask->id;
                            $all_user->save();
                        }
                    }
                }

                //--- Registrar comentarios ---//
                if ($request->comment != null || $request->comment != "") {

                    $comment = new CvComment;

                    $comment->description = $request->comment;
                    $comment->save();
                }

                //--- Relacionar comentario con la tarea ---//
                $taskByComment = new CvCommentByTask();

                $taskByComment->comment_id = ($request->comment != null) ? $comment->id : null;
                $taskByComment->task_id = $updateTask->id;
                $taskByComment->user_id = $this->userLoggedInId();
                $taskByComment->task_sub_type_id = $updateTask->task_sub_type_id;

                if ($taskByComment->save()) {

                    return [
                        "message" => "Registro actualizado",
                        "response_code" => 200,
                        "open" => false
                    ];
                }
            }
        } else {

            return [
                "message" => "La tarea no existe",
                "response_code" => 500
            ];
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 ***//
    public function destroy($id) {
        abort(404);
    }

    //*** Registrar o actualizar tareas abiertas ***//
    public function registerOrUpdateTaskOpen($request, $id) {
        
        if ($id == null) {
            $taskOpen = new CvTaskOpen();
        } else {
            $taskOpen = CvTaskOpen::find($id);
        }
        
        $allFields = $request->all();
        $proces_type = CvProcess::find($request->proccess_id);
        if($proces_type->type_process){
            $result =  $this->setContribution($allFields);
        }
        else {
            $processPsa = CvProcessTypePsa::where('proccess_id', $request->proccess_id)->get();
            if($processPsa){
                $generalPsaController = new GeneralPsaController();
                $result = $generalPsaController->insertBudget($request);
            }
            else {

                $this->createTaskOpen($request, $taskOpen);
            }
        }

        if($result['code'] == 200) {
            return $this->createTaskOpen($request, $taskOpen);
        }
        else {
            return $result ;
        }
    }


    //*** Notificacion para el registro y actualizacion de una tarea ***//
    public function notificationCreateUpdateTask($request, $task) {

        foreach ($request->user_id as $userId) {

            if ($userId != null) {
                //--- Enviar notificación ---//
                $oneSignal = new GeneralOneSignalController();

                //--- Pasar al usuario que le fue asignado la tarea ---//
                $content = "Asignación de nueva tarea";

                $oneSignal->notificationTask($userId, $task->id, $content, "general");

                //--- Guardar informacion de la notificacion ---//
                $notification = new GeneralNotificationController();

                //--- Información de la notificacion ---//
                $info = array(
                    "name" => "Asignación de nueva tarea",
                    "description" => "Realizar tarea de " . mb_strtolower(CvTask::find($task->id)->taskType->name),
                    "type" => "task",
                    "entity" => $task->id,
                    "user" => $userId
                );

                $notification->notification($info);

                //--- Enviar correo electronico al usuario que se le asigno la tarea ---//
                $emailController = new GeneralEmailController();

                //--- Parametros para la funcion email ---//
                $view = "emails.task_assigned";

                $userTask = User::find($userId);

                $infoEmail = array (
                    "email" => $userTask->email,
                    "subject" => "Asignación de una nueva tarea",
                    "title" => "Asignación de una nueva tarea",
                    "type" => $task->taskType->name,
                    "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se le ha asignado una nueva tarea para continuar con su proceso."
                );

                $emailController->sendEmail($view, $infoEmail);
            }
        }
    }

    public function setContribution($fields) {
        $generalPsaController = new GeneralPsaController();

        if (count($fields['budgetOpen']) != 0){
            foreach ($fields['budgetOpen'] as $budgetOpen){
                $result = $generalPsaController->commitedCal($budgetOpen['contribution_id'], $budgetOpen['value']);
                if ($result != 1){
                    return $result;
                }
            } 
        }
        if (count($fields['budgetOpenSpecies']) != 0){
            foreach ($fields['budgetOpenSpecies'] as $budgetOpen){
                $result = $generalPsaController->calcSpeciesCommand($budgetOpen);
                if ($result != 1){
                    return $result;
                }
            }
        }
        return [
            "message"=>"Registro exitoso",
            "code" =>200,
            "open" => true
        ];

    }

    public function createTaskOpen(Request $request, $taskOpen){

        $taskOpen->description = $request->description;
        $taskOpen->task_status_id = 1;
        $taskOpen->state = FALSE;
        $taskOpen->option_date = $request->option_date;
        $taskOpen->date_start = $request->startdate;
        $taskOpen->date_end = $request->deadline;
        $taskOpen->user_id = $request->user_id[0];
        $taskOpen->process_id = $request->proccess_id;

        switch ($request->type_process) {
            case 'psa':
                $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 32 : 2;
                break;
            case 'erosion':
                $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 33 : 26;
                break;
            case 'stards':
                $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 33 : 26;
                break;
            case 'hidrico':
                $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 34 : 21;
                break;
            //START Tareas de comunicacion
            case 'comunicacion':
                switch ($request->type_comunication){
                    case 'Encuentro con actores':
                        $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 35 : 6;
                        break;
                    case 'Plan de comunicaciones':
                        $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 36 : 7;
                        break;
                    case 'Experiencias de Educación Ambiental':
                        $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 37 : 8;
                        break;
                }
                break;
            // END Tareas de comunicacion
            default:
                // gestion abierta
                $taskOpen->task_open_sub_type_id = ($request->special == TRUE) ? 31 : 1;
                break;
        }

        //-- Valida Cuadro de Mando--//
        if (count($request->budgetOpen) !=  0){
            foreach ($request->budgetOpen as $budgetOpen){
                $contrubutionvalidate= CvAssociatedContribution::find($budgetOpen['contribution_id']);
                if (empty($contrubutionvalidate)){
                    return[
                        "message"=>"La contribucion en el cuadro de mando no existe",
                        "code"=>500
                    ];
                }
                $contrubutionvalidate->committed=$contrubutionvalidate->committed + $budgetOpen['value'];
                $contrubutionvalidate->committed_balance=$contrubutionvalidate->balance - $contrubutionvalidate->committed;
                if ( $contrubutionvalidate->committed_balance < 0){
                    return[
                        "message"=>"El saldo disponible en para la actividad no es suficiente",
                        "code"=>500
                    ];
                }
            }
        }
        if (count($request->budgetOpenSpecies) !=  0){

            foreach ($request->budgetOpenSpecies as $budgetOpenSpecy){
                $contrubutionvalidate= CvAssociatedContribution::find($budgetOpenSpecy['contributions_id']);
                if (empty($contrubutionvalidate)){
                    return[
                        "message"=>"La contribucion en el cuadro de mando no existe",
                        "code"=>500
                    ];
                }

                if ($contrubutionvalidate->type != 2){
                    return[
                        "message"=>"La contribucion en el cuadro de mando no existe",
                        "code"=>500
                    ];
                }
                $mount_totalvalidate=0;
                $specievalidate=CvContributionSpecies::find($budgetOpenSpecy['id']);
                
                if ($contrubutionvalidate->id == $specievalidate->contributions_id){
                    $mount_totalvalidate= $mount_totalvalidate + ($specievalidate->price_unit * $budgetOpenSpecy['quantity']);
                }

                $contrubutionvalidate->committed=$contrubutionvalidate->committed + $mount_totalvalidate;
                $contrubutionvalidate->committed_balance=$contrubutionvalidate->balance - $contrubutionvalidate->committed;
                if ( $contrubutionvalidate->committed_balance < 0){
                    return[
                        "message"=>"El saldo disponible en para la actividad no es suficiente",
                        "code"=>500
                    ];
                }
            }
        }

        if ($taskOpen->save()) {
            //-- back up de creacion de tarea --//
            $subtipethetask=CvTaskOpenSubType::find($taskOpen->task_open_sub_type_id);
            $created = new CvBackupTaskOpenAndEspecial();
            $created->info=$subtipethetask->name;
            $created->type=0;
            $created->task_open_id=$taskOpen->id;
            $created->to_subtype=$subtipethetask->id;
            $created->to_user=$this->userLoggedInId();
            $created->go_subtype=$subtipethetask->id;
            $created->go_user=$taskOpen->user_id;
            $created->save();
            //Se valida que la tarea abierta creada se creo con el primer subtipo, solo se puede asginar contribuyentes a una tarea en su primer subtipo
            if($taskOpen->task_open_sub_type_id == 1
                || $taskOpen->task_open_sub_type_id == 2
                || $taskOpen->task_open_sub_type_id == 26
                || $taskOpen->task_open_sub_type_id == 21
                || $taskOpen->task_open_sub_type_id == 6
                || $taskOpen->task_open_sub_type_id == 7
                || $taskOpen->task_open_sub_type_id == 8
                || $taskOpen->task_open_sub_type_id == 32
                || $taskOpen->task_open_sub_type_id == 33
                || $taskOpen->task_open_sub_type_id == 34
                || $taskOpen->task_open_sub_type_id == 35
                || $taskOpen->task_open_sub_type_id == 36
                || $taskOpen->task_open_sub_type_id == 37
                || $taskOpen->task_open_sub_type_id == 31){
                foreach ($request->budgetOpen as $budgetOpen){
                    $this->insertTaskOpenBusgetAndComandControllerTaskOpen($request, $taskOpen, $budgetOpen);
                }
                foreach ($request->budgetOpenSpecies as $budgetOpen){
                    $this->insertTaskOpenBusgetAndComandControllerTaskOpen($request, $taskOpen, $budgetOpen);
                }
            }
            $this->notificateAndEmailFinishCreateTaskOpne($request, $taskOpen);

            return [
                "message" => "Registro exitoso",
                "object_id" => $taskOpen->id,
                "code" => 200,
                "open" => true
            ];
        }
    }

    /**
     * @param Request $request
     * @param $taskOpen
     */
    private function notificateAndEmailFinishCreateTaskOpne(Request $request, $taskOpen)
    {
        $all = $request->all();
        if (array_key_exists('files', $all)) {
            $arrayFiles = $request->file('files');
            foreach ($arrayFiles as $file) {
                GeneralTaskController::saveFileOpenTask($file, $taskOpen->id, 0);
            }
        }
        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        //--- Pasar al usuario que le fue asignado la tarea ---//
        $content = "Asignación de nueva tarea abierta";

        $oneSignal->notificationTask($taskOpen->user_id, $taskOpen->id, $content, "open");

        //--- Enviar correo electronico al usuario que se le asigno la tarea ---//
        $emailController = new GeneralEmailController();

        //--- Parametros para la funcion email ---//
        $view = "emails.task_assigned";

        $userTask = User::find($taskOpen->user_id);

        $infoEmail = array(
            "email" => $userTask->email,
            "subject" => "Asignación de una nueva tarea abierta",
            "title" => "Asignación de una nueva tarea abierta",
            "type" => "Tarea abierta",
            "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se le ha asignado una nueva tarea para continuar con su proceso."
        );

        try {
            $emailController->sendEmail($view, $infoEmail);
        } catch (Exception $e) {
            //dd($e);
        }
    }

    /**
     * @param Request $request
     * @param $taskOpen
     * @param $budgetOpen
     */
    private function insertTaskOpenBusgetAndComandControllerTaskOpen(Request $request, $taskOpen, $budgetOpen)
    {
        $mount_total = 0;
        if (array_key_exists('contribution_id',$budgetOpen)){
            $contribution_id=$budgetOpen['contribution_id'];
            $valuemont = $budgetOpen['value'];
            if ($valuemont == null) {
                $valuemont = 0;
            }
        }elseif (array_key_exists('contributions_id',$budgetOpen)){
            $contribution_id=$budgetOpen['contributions_id'];
            $specie = CvContributionSpecies::find($budgetOpen['id']);
            $valuemont = $mount_total + ($specie->price_unit * $budgetOpen['quantity']);
        }else{
            $contribution_id=0;
        }
        if ($contribution_id != 0){
            //crear registro de la contribucion relacionada a la tarea.
            $cv_task_open_budgets = new CvTaskOpenBudget();
            $cv_task_open_budgets->type = $budgetOpen['contribution_type'];
            $cv_task_open_budgets->amount = $valuemont;
            $cv_task_open_budgets->task_open_id = $taskOpen->id;
            $cv_task_open_budgets->associated_contributions_id = $contribution_id;
            $cv_task_open_budgets->save();

            if ($cv_task_open_budgets->type == 2) {
                foreach ($request->budgetOpenSpecies as $budgetOpenSpecy) {
                    $cv_task_open_budget_species = new CvTaskOpenBudgetSpecies();
                    $cv_task_open_budget_species->task_open_budgets_id = $cv_task_open_budgets->id;
                    $cv_task_open_budget_species->contribution_species_id = $budgetOpenSpecy['id'];
                    $cv_task_open_budget_species->cantidad = $budgetOpenSpecy['quantity'];
                    $cv_task_open_budget_species->save();
                }
            }
        }
    }

}
