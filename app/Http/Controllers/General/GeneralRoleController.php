<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvRole;
use App\User;

class GeneralRoleController extends Controller {

    //*** Consultar los roles de contratista y guarda cuenca ***//

    public function consultCvRoles() {

        /*
         * Validar si el usuario autenticado cuenta con los siguientes roles
         * 
         * 1. Restauracion de buenas practicas
         * 2. Recurso hidrico
         * 3. Coordinacion de comunicaciones
         */

        $roles = array();
        
        switch ($this->userLoggedInRol()) {

            case 9:
            case 15:

                //--- Se filtra por el rol de coordinacion de guarda cuenca ---//
                array_push($roles, 3);

                break;
            case 10:
            case 16:

                //--- Se filtra por el rol de coordinacion de guarda cuenca ---//
                array_push($roles, 3);

                break;
            case 13:
            case 17:

                //--- Se filtra por el rol de coordinacion de guarda cuenca y contratista ---//
                array_push($roles, 3, 5);

                break;
            case 3:

                //--- Se filtra por el rol de coordinacion de guarda cuenca y equipo de seguimiento ---//
                array_push($roles, 4, 7);

                break;

            default:

                return [
                    "message" => "El usuario autenticado no tiene roles permitidos para ser visulaizados",
                    "code" => 500
                ];

        }


        return CvRole::consultCvRolesFilter($roles);
    }

    //*** Consultar todos los usuarios por rol ***//

    public function usersRole($role_id) {
        return User::consultUserByRol($role_id);
    }

    //*** Consultar los roles de guarda cuenca y equipo seguimiento ***//

    public function consultCvRoleGuardTeam() {

        return CvRole::consultCvRoleGuardTeam();
    }

    //*** Consultar todos los roles ***//

    public function consultCvRoleAll() {
        return CvRole::consultAllCvRole();
    }

}
