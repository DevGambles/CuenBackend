<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;
use App\CvRoutesAutomatic;
use App\User;
use App\CvTaskUser;

class Controller extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;


    /* --- Consult user loggedin --- */

    public function userLoggedInId() {

        return Auth::user()->id;
    }

    public function userLoggedInRol() {
        return Auth::user()->role_id;
    }

    public function userLoggedInEmail() {
        return Auth::user()->email;
    }

    /* --- Logout --- */

    public function logout_session() {

        if (Auth::check()) {
            if (is_numeric(Auth::user()->AuthAcessToken()->delete())) {
                return "Sesion cerrada";
            }
        } else {
            return "No se encuentra alguna sesión activa";
        }
    }

    //*** Routes automatics ***//

    public function routesAutomatics($info) {

        //--- Filtar las rutas a manipulas ---//
        $idRouteAutomatic = 0;

        //--- Saber el nombre del rol ---//
        $nameRol = "";

        switch ($info["permission_route"]) {

            case "permission_geo_json":

                $idRouteAutomatic = 1;
                $nameRol = "Sig";
                break;

            case "permission_geo_json_team":
                $idRouteAutomatic = 2;
                $nameRol = "Equipo de segumiento";
                break;

            case "permission_poll":
                $idRouteAutomatic = 3;
                $nameRol = "Administrativo";
                break;

            case "approve_task_property":
                $idRouteAutomatic = 4;
                $nameRol = "Equipo de segumiento";
                break;

            case "approve_task_budget":
                $idRouteAutomatic = 5;
                $nameRol = "Coordinador";
                break;

            case "request_tradition_certificate":
                $idRouteAutomatic = 6;
                $nameRol = "Guarda cuenca";
                break;

            case "task_juridico":
                $idRouteAutomatic = 7;
                $nameRol = "Juridico";
                break;

            case "task_map_validation":
                $idRouteAutomatic = 8;
                $nameRol = "Administrativo";
                break;

            case "task_minuta_coordination":
                $idRouteAutomatic = 9;
                $nameRol = "Coordinador";
                break;

            case "task_minuta_legal":
                $idRouteAutomatic = 10;
                $nameRol = "Juridico";
                break;

            case "task_minuta_direction":
                $idRouteAutomatic = 11;
                $nameRol = "Direccion";
                break;

            case "coordination_resource_good_practices":
                $idRouteAutomatic = 12;
                $nameRol = "Restauracion";
                break;

            case "task_financial":
                $idRouteAutomatic = 13;
                $nameRol = "Financiero";
                break;

            case "task_comunication":
                $idRouteAutomatic = 14;
                $nameRol = "Comunicacion";
                break;

            case "task_minuta_coordination":
                $idRouteAutomatic = 15;
                $nameRol = "Comunicacion";
                break;

            case "task_minuta_legal":
                $idRouteAutomatic = 16;
                $nameRol = "Comunicacion";
                break;

            default :

                return [
                    "message" => "Ya se realizo envio de la tarea",
                    "response_code" => 200,
                ];
        }

        //--- Consultar los usuarios con rol respectivo ---//

        $routesAutomatic = CvRoutesAutomatic::find($idRouteAutomatic);
        $usersAdmin = User::where("role_id", $routesAutomatic->role_id)->get();

        //--- Mensaje sino existen usuario con el rol indicado ---//

        if (empty($usersAdmin)) {

            return [
                "message" => "No existen usuarios con el rol de: " . $nameRol,
                "response_code" => 200,
            ];
        }

        //--- Eliminar el usuario que cuenta con la tarea asignada antes del envio ---//

        /*
         * Validar si es una tarea general o de predio potencial
         */

        $deleteExistsUsers = CvTaskUser::where("task_id", $info["task_id"])->get();

        if (!empty($deleteExistsUsers) && count($deleteExistsUsers)) {

            foreach ($deleteExistsUsers as $valueDeleteUser) {
                $valueDeleteUser->delete();
            }
        } else {

            return [
                "message" => "La tarea ya fue asignada a un usuario con rol: " . $nameRol,
                "response_code" => 200,
            ];
        }

        //--- Asignarle la tarea a todos los usuarios que cuenten con el rol indicado ---//

        foreach ($usersAdmin as $user) {

            $routesAutomatic->task_status_id;

            /*
             * Validar si es una tarea general o de predio potencial
             */

            $instanceUser = new CvTaskUser();

            if ($info["task_type_id"] == $routesAutomatic->task_type_id) {

                $instanceUser->user_id = $user->id;
                $instanceUser->task_id = $info["task_id"];

                $instanceUser->save();
            }

            //--- Por el momento guardar el historial con el primer usuario al cual se le asigno la tarea ---//
            return [
                "user" => $user->id,
                "task" => $info["task_id"]
            ];
        }
    }

}
