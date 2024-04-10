<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvNotification;
use App\User;
use App\CvPotentialProperty;
use App\Http\Controllers\General\GeneralOneSignalController;
use App\Http\Controllers\General\GeneralEmailController;

class GeneralNotificationController extends Controller {

    //*** Guardar informacion de las notificaciones ***//
    public function notification($data) {

        if ($data > 0) {

            $notification = new CvNotification();

            $notification->name = $data["name"];
            $notification->description = $data["description"];
            $notification->type = $data["type"];
            $notification->entity_id = $data["entity"];
            $notification->user_id = $data["user"];

            if ($notification->save()) {
                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            }
        }
    }

    //*** Notificaciones para el envio de la tarea ***//
    public function notificationTask($task, $content, $userId) {

        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        //--- Pasar al usuario que le fue asignado la tarea ---//

        $oneSignal->notificationTask($userId, $task->id, $content, "general");

        //--- Guardar informacion de la notificacion ---//

        $notification = new GeneralNotificationController();

        //--- Información de la notificacion ---//
        $info = array(
            "name" => "Registro exitoso de encuesta",
            "description" => "El guarda cuenca " . User::find($userId)->name . " ha realizado la encuesta del "
            . "procedimiento " . $task->process[0]->name . ".",
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

        //--- Filtar el tipo de la tarea para el asunto y titulo del correo ---//

        $tituleContent = "";

        switch ($task->taskType->id) {

            //--- Encuesta ---//

            case 1:
                $tituleContent = "encuesta";
                break;

            //--- Medicion ---//

            case 4:
                $tituleContent = "medición";
                break;

            default:
                break;
        }

        $infoEmail = array(
            "email" => $userTask->email,
            "subject" => "Registro de una nueva encuesta" . $tituleContent,
            "title" => "Registro de una nueva " . $tituleContent,
            "type" => $task->taskType->name,
            "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se le ha asignado una nueva tarea para continuar con su proceso."
        );

        $emailController->sendEmail($view, $infoEmail);
    }

    //***Notificaciones para el envio del predio potencial ***//
    public function notificationFlowPotential($potential_id, $user_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (empty($potential)) {
            return[
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        $content = "Se ha aprobado el predio potencial de " . $potential->property_name;

        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        //--- Pasar al usuario que le fue asignado la tarea ---//

        $oneSignal->notificationPotential($user_id, $potential->id, $content);

        /*
         * Guardar informacion de la notificacion
         * Información de la notificacion
         */

        $info = array(
            "name" => "Flujo del predio potencial",
            "description" => "Se ha aprobado el predio potencial de " . $potential->name,
            "type" => "potential",
            "entity" => $potential->id,
            "user" => $user_id
        );

        $this->notification($info);
    }

    //*** Envio de email para notificacion del flujo del predio potencial ***//

    public function MailPotentialApprove($id_potential, $user_id) {

        $property = CvPotentialProperty::find($id_potential);

        if (!$property) {
            return 500;
        }

        //--- Enviar correo electronico al usuario que se le asigno la tarea ---//
        $emailController = new GeneralEmailController();

        //--- Vista ---//
        $view = "emails.potential_approved";

        $user = User::find($user_id);

        $infoEmail = array(
            "email" => $user->email,
            "subject" => "Registro de Predio",
            "title" => " ",
            "type" => "Predio Registrado",
            "description" => $user->names . " " . $user->last_names . " el predio " . $property->property_name . " se ha registrado Exitosamente."
        );

        $emailController->sendEmail($view, $infoEmail);

        return 200;
    }

}
