<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvTask;
use App\CvProcess;
use App\CvTaskUser;
use App\Http\Controllers\General\GeneralHistoryTaskController;
use App\Http\Controllers\TaskController;

class GeneralFlowController extends Controller {

    //*** Cancelar tareas del procedimiento de tareas y encuesta ***//

    public function cancelProcessTaskByProperty($id) {

        //--- Validar si el usuario cuenta con el rol establecido para realizar esta accion ---//

        switch ($this->userLoggedInRol()) {
            case 12:
            case 10:
            case 9:
            case 15:
            case 16:
                $state = true;
                break;

            default:
                $state = false;
                return [
                    "message" => "El usuario no cuenta con el rol de establecido para realizar esta accion",
                    "response_code" => 500
                ];
        }

        if ($state == true) {

            //--- Informacion de la tarea ---//
            $task = CvTask::find($id);

            if (!empty($task)) {

                //--- Consultar las tareas que cuenta el procedimiento ---//
                $processWithTask = CvProcess::find($task->process[0]->id)->processByTasks;

                //--- Eliminar las tareas que cuenten con el tipo de tarea de encuesta (3) y de mapa (1) ---//
                foreach ($processWithTask as $task) {

                    if ($task->task_type_id == 3 || $task->task_type_id == 1) {
                        $task->state = 1;
                        $task->save();
                    }
                }

                return [
                    "message" => "El procedimiento con las tareas de encuesta y mapa han sido canceladas con exito",
                    "response_code" => 200
                ];
            } else {

                return [
                    "message" => "La tarea no existe en el sistema",
                    "response_code" => 500
                ];
            }
        }
    }

    //*** Aprobaciones y rechazos personalizados ***//
    //--- Asignar la tarea a usuarios de seguimiento o guarda cuenca en el sub tipo de firma minuta ---//

    public function assignTaskGuardTeamFirm(Request $request) {

        $deleteTaskUser = CvTaskUser::where("task_id", $request->task_id)->where("user_id", $this->userLoggedInId())->first();

        //--- Eliminar la tarea al usuario que esta autenticado para asignarsela al nuevo usuario ---//

        if (empty($deleteTaskUser)) {
            return [
                "message" => "El usuario actual no cuenta con la tarea asignada",
                "response_code" => 500
            ];
        }

        $deleteTaskUser->delete();

        //--- Asignar la tarea al nuevo usuario ---//
        $taskByUser = new CvTaskUser();
        $taskByUser->task_id = $request->task_id;
        $taskByUser->user_id = $request->user_id;

        if ($taskByUser->save()) {

            $historyTask = array();

            $task = CvTask::find($request->task_id);
            $task->task_sub_type_id = 32;
            $task->save();

            // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
            $historyController = new GeneralHistoryTaskController();
            $taskController = new TaskController();

            //--- Guardar registro en el historial ---//
            array_push($historyTask, array(
                "type_task" => "General_task",
                "info" => $taskController->show($task->id),
                "map" => $task->geoJson,
                "property" => $task->property,
                "status" => $task->task_status_id,
                "task_id" => $task->id,
                "user_from" => $this->userLoggedInId(),
                "user_to" => $request->user_id
                    )
            );

            if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                return [
                    "message" => "La tarea ha sido asignada exitosamente",
                    "response_code" => 200
                ];
            }
        }
    }

}
