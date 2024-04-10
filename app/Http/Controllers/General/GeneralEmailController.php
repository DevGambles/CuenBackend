<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mail;

class GeneralEmailController extends Controller {

    //*** Funcion para enviar correos electronicos en general ***//

    public function sendEmail($view, $info) {

        Mail::send($view, $info, function ($msj) use ($info) {
            $msj->subject($info["subject"]);
            $msj->to($info["email"]);

            // Get the underlying SwiftMailer message instance...
            if ($msj->getSwiftMessage()) {
                return 500;
            }
        });

        $errors_send = array();

        if (count(Mail::failures()) > 0) {

            foreach (Mail::failures() as $email_address_error) {

                array_push($errors_send, $email_address_error);
            }
        }

        if (count($errors_send) > 0) {
            return $errors_send;
        }

        return 200;
    }

}
