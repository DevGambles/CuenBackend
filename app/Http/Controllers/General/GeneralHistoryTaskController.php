<?php

namespace App\Http\Controllers\General;

use App\CvBackupFlowTasks;
use App\CvTask;
use App\CvTaskUser;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralOneSignalController;
use App\Http\Controllers\General\GeneralEmailController;
use App\User;
use App\CvGeoJsonUser;

class GeneralHistoryTaskController extends Controller {

    // *** Guardar el historal de la tarea cuando se realice cualquier acción sobre ella *** //

    public function saveHistoryTask($response) {

        $backupTaks = new CvBackupFlowTasks();

        // *** Filtrar en que paso se realiza el registro de la tarea y las acciones a tomar *** //

        switch ($response["type_task"]) {

            case "Register_task":

                $backupTaks->info_task = json_encode($response["info"]);
                $backupTaks->info_user_from = $response["user_from"];
                $backupTaks->info_user_to = $response["user_to"];
                $backupTaks->info_task_id = $response["task_id"];

                break;

            case "GeoJson_task":

                $backupTaks->info_map_geo_json = json_encode($response["info"]);
                $backupTaks->info_user_from = $response["user_from"];
                $backupTaks->info_user_to = $response["user_to"];
                $backupTaks->info_task_id = $response["task_id"];

                break;

            case "Register_task_property":

                $backupTaks->info_property = json_encode($response["info"]);
                $backupTaks->info_user_from = $response["user_from"];
                $backupTaks->info_user_to = $response["user_to"];
                $backupTaks->info_task_id = $response["task_id"];

                break;

            case "Back_task":

                $backupTaks->info_task = json_encode($response["info"]);
                $backupTaks->info_map_geo_json = json_encode($response["map"]);
                $backupTaks->info_property = json_encode($response["property"]);
                $backupTaks->info_user_from = $response["user_from"];
                $backupTaks->info_user_to = $response["user_to"];
                $backupTaks->info_task_id = $response["task_id"];

                break;

            case "General_task":

                $backupTaks->info_task = json_encode($response["info"]);
                $backupTaks->info_map_geo_json = json_encode($response["map"]);
                $backupTaks->info_property = json_encode($response["property"]);
                $backupTaks->info_user_from = $response["user_from"];
                $backupTaks->info_user_to = $response["user_to"];
                $backupTaks->info_task_id = $response["task_id"];

                break;

            default:

                return [
                    "message" => "La opcion en el backup del historial de tareas no existe",
                    "response_code" => 200
                ];
        }

        // *** Guardar el registro de la tarea *** //

        if ($backupTaks->save()) {
            return 200;
        }
    }

    // *** Flujo de devolver tareas entre usuario *** //

    public function backTaskByUser($id) {

        //--- Buscar el ultimo registro del historial de la tarea ---//
        $historyTask = CvBackupFlowTasks::where("info_task_id", $id)->orderBy('id', 'desc')->first();

        //--- Obtener los usuarios desde y hasta cual fue enviada la tarea ---//
        if (empty($historyTask)) {

            return [
                "message" => "La tarea no existe en el sistema",
                "code" => 500
            ];
        }

        $userFrom = $historyTask->info_user_from;
        $userTo = $historyTask->info_user_to;

        // --- Validar que el usuario autenticado sea el que puede devolver la tarea --- //

        if ($this->userLoggedInId() != $userTo) {
            return [
                "message" => "El usuario no puede devolver la tarea ya que no se le ha sido asignada",
                "code" => 500,
            ];
        }

        //--- Encontrar la tarea relacionada con el usuario --- //
        $taskByUser = CvTaskUser::where("task_id", $id)->where("user_id", $userTo)->first();

        /*
         * Validar si la tarea se encuentra en el sub tipo 5 de "Edicion de medicion"
         */
        $taskMap = CvTask::find($id);

        if ($taskMap->task_sub_type_id == 5) {

            $usersId = $this->backTaskSigToGuardMultiple($id, $taskByUser);
        } else {

            //--- Guardar el usuario al cual se le devolvio la tarea asignada ---//
            $taskByUser->user_id = $userFrom;
            $taskByUser->save();
        }

        //--- Enviar información de la tarea para registrar su historial ---//
        $historyTaskSend = array();

        $infoTask = CvTask::find($id);

        //--- Validar si la tarea cuenta con informacion de mapa y encuesta --- //
        $map = $infoTask->geoJson;
        $property = $infoTask->property;

        array_push($historyTaskSend, array(
            "type_task" => "Back_task",
            "info" => $infoTask,
            "map" => $map,
            "property" => $property,
            "task_id" => $id,
            "user_from" => $this->userLoggedInId(),
            "user_to" => (!empty($usersId)) ? $usersId : $userFrom
                )
        );

        // --- Cambiar el sub tipo de la tarea --- //
        $subTypeTask = new GeneralSubTypeTaskController();

        $valueSubType = $subTypeTask->changeSubTypeTask($infoTask->task_sub_type_id, $this->userLoggedInId());

        if (is_string($valueSubType)) {

            //--- Retornar el antiguo sub tipo si falla el cambio del sub tipo de tarea ---//

            $taskByUser->user_id = $userTo;
            $taskByUser->save();
            return $valueSubType;
        }

        $infoTask->task_sub_type_id = $valueSubType;
        $infoTask->save();

        /*
         * Enviar notificaciones de la tarea cuando es regresada
         */

        $this->notificationBackTask($infoTask);
        $this->notificationEmailBackTask($infoTask, $userFrom);

        //--- Se capturo una excepción al asginar un subtipo ---//

        if (is_string($valueSubType)) {
            return $valueSubType;
        }

        if ($this->saveHistoryTask($historyTaskSend[0]) == 200) {

            return [
                "message" => "La tarea ha sido devuelta",
                "code" => 200,
            ];
        } 
    }

    //*** Enviar notificaciones cada vez que se regresa la tarea ***//
    public function notificationBackTask($task) {

        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        $content = "Se ha regresado una tarea con:  " . $task->taskSubType->name . ". Procedimiento: " . $task->process[0]->name;

        $sendNotificationCount = 0;

        foreach ($task->user as $users) {
            $sendNotificationCount++;
            $oneSignal->notificationTask($users->id, $task->id, $content, "general");
        }
    }

    //*** Enviar email cada vez que se regresa la tarea ***//
    public function notificationEmailBackTask($task, $userId) {

        //--- Enviar correo electronico al usuario que se le asigno la tarea ---//

        $emailController = new GeneralEmailController();

        //--- Parametros para la funcion email ---//

        $view = "emails.task_assigned";

        $userTask = User::find($userId);

        $infoEmail = array(
            "email" => $userTask->email,
            "subject" => "Devolución de una nueva tarea",
            "title" => "Devolución de una nueva tarea",
            "type" => $task->taskType->name,
            "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se "
            . "le ha devuelto una tarea en el flujo con el sub tipo " . $task->taskSubType->name . " para continuar con su proceso en el procedimiento"
            . "de " . $task->process[0]->name . "."
        );

        $emailController->sendEmail($view, $infoEmail);
    }

    //*** Devolver la tarea desde sig a multiples guarda cuencas que registraron las mediciones ***//
    public function backTaskSigToGuardMultiple($taskId, $taskByUser) {

        if ($taskByUser->delete()) {

            /*
             * Encontrar las mediciones de los usuarios vinculados a la tarea y cambiar su estado
             * Posteriormente vincular los usuarios a la tarea
             */

            $taskGeoJsonUsers = CvGeoJsonUser::where("task_id", $taskId)->get();

            foreach ($taskGeoJsonUsers as $valueTaskGeoJsonUsers) {

                //--- Vincular la tarea al usuario ---//
                $taskByUser = new CvTaskUser();
                $taskByUser->task_id = $taskId;
                $taskByUser->user_id = $valueTaskGeoJsonUsers->user_id;
                $taskByUser->save();

                //--- Borrar las mediciones de cada geo json ---//
                $valueTaskGeoJsonUsers->delete();
            }
        }
    }

}
