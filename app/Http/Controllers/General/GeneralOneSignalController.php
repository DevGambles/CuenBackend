<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\NotificationOneSignal;
use App\PlayerIdOneSignal;
use App\Http\Requests\OneSignalRequest;
use App\User;
use App\CvTask;
use App\CvLetterIntention;

class GeneralOneSignalController extends Controller {

    //*** Obtener player_id de one signal y relacionarlo con el usuario logueado ***//

    public function getPlayerIdOneSignal(OneSignalRequest $request) {

        $playerIdOneSignal = new PlayerIdOneSignal();

        $playerIdOneSignal->player_id = $request->player_id;
        $playerIdOneSignal->user_id = $this->userLoggedInId();

        if ($playerIdOneSignal->save()) {

            return [
                "message" => "Registro exitoso",
                "code" => 200
            ];
        }
    }

    //*** Funcion general para el envio de la notificacion del flujo de tarea ***//

    public function notificationTask($userId, $taskId, $content, $optionTask) {

        $user = User::find($userId);

        if (!empty($user->playerId)) {

            $userPlayerId = $user->playerId;

            /*
             * Validar la opcion de la tarea
             * 
             * General: Tareas de medicion y encuesta
             * open: Tarea de abierta
             * 
             */

            switch ($optionTask) {

                case "general":
                    $typeTask = CvTask::find($taskId)->taskType;
                    break;

                case "open":
                    $typeTask = "Tarea abierta";
                    break;

                case "intention":

                    $typeTask = CvLetterIntention::find($taskId)->taskType;
                    break;

                default:
                    break;
            }

            if (!empty($userPlayerId) && !empty($typeTask)) {

                //--- Validar si el usuario cuenta con un player id de one signal ---//

                $notificationOneSignal = new NotificationOneSignal();

                $options = array();

                $contentInfo = $content;
                $text = (is_string($typeTask)) ? $typeTask : mb_strtolower($typeTask->name);
                $icon = "https://sgc.cuencaverde.org/sieeve-brand-white.3d3bebc0ad3fd563d8d9.svg";
                $url = "";

                $playersIds = array(
                    $userPlayerId->player_id
                );

                array_push($options, array(
                    "id" => "read-more-button",
                    "text" => $text,
                    "icon" => $icon,
                    "url" => $url
                ));

                //--- Información adicional ---//

                $data = array(
                    "type" => "task",
                    "entity_id" => $taskId
                );

                //--- Parametros generales ---//

                return $notificationOneSignal->createNotification($contentInfo, $playersIds, $options, $data);
            }
        }
    }

    //*** Funcion general para el envio de la notificacion del flujo de predio potencial ***//

    public function notificationPotential($userId, $potentialId, $content) {

        $user = User::find($userId);
        
        if (!empty($user->playerId)) {

            $userPlayerId = $user->playerId;

            if (!empty($userPlayerId)) {

                //--- Validar si el usuario cuenta con un player id de one signal ---//
                $notificationOneSignal = new NotificationOneSignal();

                $options = array();

                $contentInfo = $content;
                $text = "Predio potencial";
                $icon = "https://sgc.cuencaverde.org/sieeve-brand-white.3d3bebc0ad3fd563d8d9.svg";
                $url = "";

                $playersIds = array(
                    $userPlayerId->player_id
                );

                array_push($options, array(
                    "id" => "read-more-button",
                    "text" => $text,
                    "icon" => $icon,
                    "url" => $url
                ));

                //--- Información adicional ---//
                $data = array(
                    "type" => "potential",
                    "entity_id" => $potentialId
                );

                //--- Parametros generales ---//
                return $notificationOneSignal->createNotification($contentInfo, $playersIds, $options, $data);
            }
        }
    }

    //*** Obtener el player_id y vincularlo al usuario autenticado ***//

    public function getPlayerIdUserAuth(OneSignalRequest $request) {

        ($playerIdUserExist = PlayerIdOneSignal::where("player_id", $request->player_id)->where("user_id", $this->userLoggedInId())->exists());

        if ($playerIdUserExist != true) {

            $newPlayerId = new PlayerIdOneSignal();

            $newPlayerId->player_id = $request->player_id;
            $newPlayerId->user_id = $this->userLoggedInId();

            if ($newPlayerId->save()) {

                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            }
        }

        return [
            "message" => "El usuario ya cuenta con el player_id en el sistema",
            "code" => 500
        ];
    }

}
