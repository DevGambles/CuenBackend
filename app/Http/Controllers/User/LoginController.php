<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Auth;
use App\OauthAccessToken;

class LoginController extends Controller {

    //*** Cerrar sesion ***//

    public function logout() {

        if (Auth::check()) {
            if (is_numeric(Auth::user()->AuthAcessToken()->delete())) {
                return "Sesion cerrada";
            }
        } else {
            return "No se encuentra alguna sesión activa";
        }
    }

}
