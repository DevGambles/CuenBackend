<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvLetterIntention;
use App\CvProcess;
use App\User;
use App\Http\Controllers\General\GeneralEmailController;

class GeneralLetterIntentionController extends Controller {

    //*** Registrar carta de intencion ***//

    public function registerLetterIntention($request) {

        //--- Validar si existe el procedimiento ---//

        $validateProcess = CvProcess::find($request->proccess_id);

        if (empty($validateProcess)) {
            return [
                "message" => "El procedimiento no existe en el sistema, para la carta de intencion",
                "code" => 200
            ];
        }

        //--- Validar si existe el usuario ---//

        $validateUsers = User::find($request->user_id);

        if (empty($validateUsers)) {
            return [
                "message" => "El usuario no existe en el sistema, para la carta de intencion",
                "code" => 200
            ];
        }

        $letterIntention = new CvLetterIntention();

        $letterIntention->title = $request->title;
        $letterIntention->form_letter = "";
        $letterIntention->process_id = $request->proccess_id;
        $letterIntention->process_id = $request->proccess_id;
        $letterIntention->user_id = $request->user_id;
        $letterIntention->type_id = $request->type_id;

        if ($letterIntention->save()) {

            //--- Notificacion one signal y email ---//
            $this->sendNotificationTaskLetterIntention($letterIntention);
            $this->sendEmailTaskLetterIntention($letterIntention);

            return [
                "message" => "Registro exitoso",
                "response_code" => 200,
                "object_id" => $letterIntention->id,
                "sub_type_id" => 0,
                "sub_type_name" => "Carta de intención"
            ];
        }
    }

    //*** Actualizar carta de intencion ***//

    public function updateLetterIntention(Request $request, $id) {

        $letterIntention = CvLetterIntention::find($id);

        // --- Validar que el usuario autenticado sea igual al usuario que tiene la carta de intencion --- //

        switch ($request->type_update) {

            //--- Actualizacion en movil ---//

            case 0:

                if ($letterIntention->user_id != $this->userLoggedInId()) {
                    return [
                        "message" => "El usuario logueado no coincide con el usuario asignado de la carta de intencion",
                        "code" => 200
                    ];
                }

                $letterIntention->form_letter = json_encode($request->form_letter);
                $letterIntention->user_id = $this->userLoggedInId();

                break;

            //--- Actualizacion en web ---//

            case 1:

                $letterIntention->user_id = $request->user_id;

                break;

            default:

                return [
                    "message" => "No se envio un tipo de edicion valida",
                    "code" => 200
                ];
        }

        if ($letterIntention->save()) {
            return [
                "message" => "Actualizacion exitosa",
                "code" => 200
            ];
        }
    }

    //*** Consultar cartas de intencion ***//

    public function consultLetterIntention() {

        //--- Consulta de todos los monitoreos registros en el sistema ---//

        switch ($this->userLoggedInId()) {

            case 3:
                $lettersIntention = CvLetterIntention::get();

                break;

            default:

                $lettersIntention = CvLetterIntention::where("user_id", $this->userLoggedInId())->orderBy('created_at', 'DESC')->get();

                break;
        }


        //--- Personalizar respuesta ---//

        foreach ($lettersIntention as $letterIntention) {

            $letterIntention["task_type_id"] = 5;
            $letterIntention->process;
            $letterIntention["sub_type"] = [
                "name" => "Carta de intención"
            ];
        }

        return $lettersIntention;
    }

    //*** Consultar cartas de intencion en especifica ***//

    public function consultLetterIntentionSpecific($id) {

        $letterIntention = CvLetterIntention::find($id);

        if (empty($letterIntention)) {
            return [
                "message" => "La carta de intencion no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Personalizar respuesta ---//

        $letterIntention["task_type_id"] = 5;
        $letterIntention["task_type_name"] = "Carta de intención";
        $letterIntention["validation"] = (!empty($letterIntention->form_letter)) ? true : false;
        $letterIntention->process;
        $letterIntention->User;


        return [$letterIntention];
    }

    //*** Validar carta de intencion en proceso ***//

    public function validateLetterProccess($id) {

        $letterIntention = CvLetterIntention::find($id);

        if (empty($letterIntention)) {
            return [
                "message" => "La carta de intencion no existe en el sistema",
                "code" => 500
            ];
        }

        return (!empty($letterIntention->form_letter)) ? $letterIntention->form_letter : [];
    }

    //*** Enviar notificacion para carta de intencion tanto para registrar como editar ***//

    public function sendNotificationTaskLetterIntention($letterIntention) {

        //--- Enviar notificación ---//
        $oneSignal = new GeneralOneSignalController();

        //--- Pasar al usuario que le fue asignado la tarea ---//

        $content = "Asignación de nueva tarea de carta de intención";

        return $oneSignal->notificationTask($letterIntention->user_id, $letterIntention->id, $content, "intention");
    }

    //*** Enviar notificacion de correo electronico para carta de intencion tanto para registrar como editar ***//

    public function sendEmailTaskLetterIntention($letterIntention) {

        //--- Enviar correo electronico al usuario que se le asigno la tarea ---//

        $emailController = new GeneralEmailController();

        //--- Parametros para la funcion email ---//

        $view = "emails.task_assigned";

        $userTask = User::find($letterIntention->user_id);

        $infoEmail = array(
            "email" => $userTask->email,
            "subject" => "Asignación de una nueva tarea",
            "title" => "Asignación de una nueva tarea",
            "type" => $letterIntention->taskType->name,
            "description" => $userTask->names . " " . $userTask->last_names . " con el rol " . $userTask->role->name . " se le ha asignado una nueva tarea para continuar con su proceso."
        );

        $emailController->sendEmail($view, $infoEmail);
    }

}
