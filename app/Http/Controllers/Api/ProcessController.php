<?php

namespace App\Http\Controllers\Api;

use App\CvProcess;
use App\CvTask;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;

class ProcessController extends Controller
{

    //*** Consultar la información de los procedimientos ***//

    public function infoProcess()
    {

        $info = array();
        $idTaskArray = array();

        $arrayProcess = array();

        $carbon = Carbon::now();

        switch ($this->userLoggedInRol()) {

            case 4:

                // --- Si el usuario no cuenta con alguna tarea no podra visualizar los procesos --- //
                $user_tasks = User::find($this->userLoggedInId());

                $date = array();

                foreach ($user_tasks->usertaskOpen as $taskOpen) {
                    $taskOpen['type'] = 'open';
                    if (count($info) > 0) {
                        $insert = 0;
                        foreach ($info as $datainfo) {
                            if ($taskOpen->process_id == $datainfo['process_id']) {
                                $insert = 1;
                            }
                        }
                        if ($insert == 0) {

                            //=====================================================================
                            // Se reemplazan valores de tareas abiertas por datos del procedimiento
                            //=====================================================================

                            $taskOpen['name'] = CvProcess::find($taskOpen->process_id)->name;
                            $taskOpen['id'] = CvProcess::find($taskOpen->process_id)->id;
                            $taskOpen['description'] = CvProcess::find($taskOpen->process_id)->description;

                            //=================================================
                            // Se eliminan datos de tareas abiertas inecesarios
                            //=================================================

                            unset($taskOpen['date_start']);
                            unset($taskOpen['date_end']);
                            unset($taskOpen['option_date']);
                            unset($taskOpen['state']);
                            unset($taskOpen['task_status_id']);
                            unset($taskOpen['task_open_sub_type_id']);

                            array_push($info, $taskOpen);
                            array_push($arrayProcess, $taskOpen->process_id);
                        }
                    } else {

                        //=====================================================================
                        // Se reemplazan valores de tareas abiertas por datos del procedimiento
                        //=====================================================================

                        $taskOpen['name'] = CvProcess::find($taskOpen->process_id)->name;
                        $taskOpen['id'] = CvProcess::find($taskOpen->process_id)->id;
                        $taskOpen['description'] = CvProcess::find($taskOpen->process_id)->description;

                        //=================================================
                        // Se eliminan datos de tareas abiertas inecesarios
                        //=================================================

                        unset($taskOpen['date_start']);
                        unset($taskOpen['date_end']);
                        unset($taskOpen['option_date']);
                        unset($taskOpen['state']);
                        unset($taskOpen['task_status_id']);
                        unset($taskOpen['task_open_sub_type_id']);

                        array_push($info, $taskOpen);
                        array_push($arrayProcess, $taskOpen->process_id);
                    }

                }

                if (!empty($user_tasks->task)) {

                    $user_tasks = $user_tasks->task;

                    if (count($user_tasks) > 0) {

                        //--- Obtener la fecha de actualizacion y el id de la tarea ---//
                        foreach ($user_tasks as $task) {

                            array_push($date, $carbon->format($task->updated_at) . "/" . $task->id);
                        }

                        //--- Ordernar fechas de mayor a menor y guardar los Id's de la tarea ---//
                        arsort($date);
                        foreach ($date as $valor) {
                            $ids = explode('/', $valor);
                            array_push($idTaskArray, $ids[1]);
                        }
                        //--- Guardar la informacion de los procedimientos de cada tarea ---//

                        foreach ($idTaskArray as $taskId) {

                            $listsProcess = CvTask::find($taskId)->process;
                            $listsProcess[0]['type'] = 'normal';
                            $listsProcess[0]['process_id'] = $listsProcess[0]->pivot->process_id;
                            array_push($info, $listsProcess[0]);
                            array_push($arrayProcess, $listsProcess[0]['process_id']);
                        }
                    }
                    break;
                }

            default:

                return [
                    "message" => "El usuario no cuenta con rol de guarda cuenca",
                    "response_code" => 200,
                ];
        }

        if (!empty($info)) {

            //=======================================================================
            // Consultar el total de todas las tareas generales, ejecucion y abiertas
            //=======================================================================
            $totalTaskByProcessArray = array_count_values($arrayProcess);

            foreach ($totalTaskByProcessArray as $key => $itemTotalTaskByProcessArray) {

                $processTasksNormal = CvProcess::find($key)->processByTasks;
                $processTasksOpen = CvProcess::find($key)->taskOpenProcess;

                //===========================================
                // Omitir la tarea de encuesta para el conteo
                //===========================================

                if (!empty($processTasksNormal)) {
                    foreach ($processTasksNormal as $keyProccessTask => $itemProcessTasksNormal) {
                        if ($itemProcessTasksNormal->task_sub_type_id == 3) {
                            unset($processTasksNormal[$keyProccessTask]);
                        }
                    }
                }

                foreach ($info as $itemInfo) {
                    if ($itemInfo->process_id == $key) {

                        $processTasksOpenCount = $processTasksOpen->where("user_id", $this->userLoggedInId())->count();
                        $processTasksNormalCount = $processTasksNormal->count();

                        $sumTotalTasks = (int)$processTasksOpenCount + (int)$processTasksNormalCount; 

                        $itemInfo["total_count_task"] = (int)$processTasksNormalCount;
                    }
                }

            }

            return $info;

        } else {
            return [];
        }
    }

}
