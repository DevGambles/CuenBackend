<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CvComment;
use App\CvCommentByTask;
use App\CvTask;
use Carbon\Carbon;
use App\CvProcess;

class GeneralCommentsbyTask extends Controller {

    //*** Funcion para consultar comentarios by task ***//

    public function consultCommentsbyTask($id, $type) {

        $task = CvTask::find($id);

        if (!empty($task)) {

            //--- Consulta de las tareas de un procedimiento ---//

            $process = CvProcess::find($task->process[0]->id);

            $taskByProcess = $process->processByTasks;

            //--- Consultar todos los comentarios de la tarea y los sub tipos ---//

            $info = array();

            foreach ($taskByProcess as $task) {

                $commentsByTask = CvTask::find($task->id);

                foreach ($commentsByTask->comment as $commentByTask) {

                    $user = CvCommentByTask::where("comment_id", $commentByTask->pivot->comment_id)->first()->user;

                    $filterSubType = array();

                    switch ($type) {

                        case 1:

                            array_push($filterSubType, $type, "2");

                            break;

                        case 4:

                            array_push($filterSubType, $type, "5", "3");

                            break;

                        case 2:

                            array_push($filterSubType, $type, "5", "3", "4");

                            break;

                        case 3:

                            array_push($filterSubType, $type, "1", "2", "3", "8", "9", "10");

                            break;

                        case 5:

                            array_push($filterSubType, $type, "5", "3", "4");

                            break;

                        case 8:

                            array_push($filterSubType, $type, "5", "3", "4");

                            break;

                        case 9:

                            array_push($filterSubType, $type, "5", "3", "4");

                            break;

                        case 6:

                            array_push($filterSubType, $type, "5", "3", "4", "1");

                            break;

                        case 10:

                            array_push($filterSubType, $type, "2");

                            break;

                        case 11:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "6");

                            break;

                        case 13:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11");

                            break;

                        case 15:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13");

                            break;

                        case 24:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15");

                            break;

                        case 25:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24");

                            break;

                        case 26:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25");

                            break;

                        case 27:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26");

                            break;

                        case 28:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27");

                            break;

                        case 29:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28");

                            break;

                        case 14:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29");

                            break;

                        case 22:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14");

                            break;

                        case 20:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22");

                            break;

                        case 16:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20");

                            break;

                        case 21:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20", "16");

                            break;

                        case 32:

                            array_push($filterSubType, $type, "5", "3", "4", "1", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20", "16", "21");

                            break;

                        case 33:

                            array_push($filterSubType, $type,"1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32");

                            break;

                        default:

                            return [
                                "message" => "No existe la opcion de sub tipo en los comentarios",
                                "response_code" => 200
                            ];
                    }

                    // *** Filtrar los comentarios por los sub tipos *** //

                    foreach ($filterSubType as $filter) {

                        $date = Carbon::now();

                        if ($task->task_sub_type_id == $filter) {

                            array_push($info, array(
                                "comment_id" => $commentByTask->id,
                                "description" => $commentByTask->description,
                                "user_id" => $commentByTask->description,
                                "user_name" => $user->name,
                                "created_at" => $date->format($commentByTask->created_at)
                            ));
                        }
                    }
                }
            }

            return $info;
        }

        return [
            "message" => "La tarea no existe en el sistema",
            "response_code" => 200
        ];
    }

    public function createCommentsbyTask(Request $request) {

        //--- Instaciar el modelo comentario ---//

        if ($request->task_id != "") {

            $comment = new CvComment();

            $comment->description = $request->comment;

            if ($comment->save()) {

                $commentByTask = new CvCommentByTask();

                $commentByTask->comment_id = $comment->id;
                $commentByTask->task_id = $request->task_id;
                $commentByTask->user_id = $this->userLoggedInId();
                $commentByTask->task_sub_type_id = $request->sub_type;

                if ($commentByTask->save()) {

                    return [
                        "message" => "Registro exitoso",
                        "response_code" => 200
                    ];
                }
            }
        }
    }

}
