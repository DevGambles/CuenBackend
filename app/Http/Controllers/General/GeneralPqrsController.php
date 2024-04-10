<?php

namespace App\Http\Controllers\General;

use function GuzzleHttp\Promise\all;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvTypePqrs;
use App\CvPqrs;
use App\CvResponsePqrs;
use App\Http\Controllers\General\GeneralPropertyController;
use App\Http\Controllers\General\GeneralEmailController;
use App\CvRolePqrs;

class GeneralPqrsController extends Controller {

    //*** Registrar dependencias o roles que va hacer seleccionadas en PQRS ***//

    public function dependenciesRolesPqrs(Request $request) {

        $register = 0;

        if (!empty($request->role_id)) {

            foreach ($request->role_id as $role) {

                $rolePqrs = new CvRolePqrs();
                $rolePqrs->dependencies_role_id = $role;

                if ($rolePqrs->save()) {
                    $register = 1;
                }
            }

            if ($register == 1) {
                return [
                    "message" => "Registro exitoso",
                    "response_code" => 200
                ];
            }
        }
    }

    //*** Consultar dependecias o roles del pqrs ***//

    public function consultDependencies() {

        $rolePqrs = CvRolePqrs::orderBy("dependencies_role_id")->get();
        $info = array();

        foreach ($rolePqrs as $dependencie) {
            $dependencie->role;
            array_push($info, $dependencie->role);
        }

        if (!empty($rolePqrs)) {
            return $info;
        }

        return [
            "message" => "No hay dependecias o roles registrados para los pqrs",
            "response_code" => 200
        ];
    }

    //*** Consultar PQRS de acuardo al usuario autenticado ***//

    public function consultPQRS() {

        $roledependence = CvRolePqrs::where('dependencies_role_id',$this->userLoggedInRol())->first();

        $pqrs=CvPqrs::all();
        foreach ($pqrs as $detailpqr){
            if ($detailpqr->state == 1){
                $detailpqr['pqrs_state']="Resuelto";
            }else{
                $detailpqr['pqrs_state']="Sin Resolver";
            }
        }
        // if ($roledependence != null || $this->userLoggedInRol() == 13 || $this->userLoggedInRol() == 17)
        // {
        //     if ($this->userLoggedInRol() == 13 || $this->userLoggedInRol() == 17){
        //         $pqrs=CvPqrs::all();
        //     }else{
        //         $pqrs = CvPqrs::where('dependencies_role_id',$roledependence->id)->get();
        //     }
        //     foreach ($pqrs as $detailpqr){
        //         if ($detailpqr->state == 1){
        //             $detailpqr['pqrs_state']="Resuelto";
        //         }else{
        //             $detailpqr['pqrs_state']="Sin Resolver";
        //         }
        //     }
        // } else
        // {
        //     $pqrs = [];
        // }
        return $pqrs;
    }

    //*** Consultar PQRS con su informacion especifica ***//

    public function consultSpecificPQRS($id) {

        $info = array();

        $pqrs = CvPqrs::find($id);

        if (empty($pqrs)) {
            return [
                "message" => "El pqrs no se encuentra en el sistema",
                "response_code" => 200
            ];
        }
        if ($pqrs->state == 1){
            $pqrs['pqrs_state']="Resuelto";
        }else{
            $pqrs['pqrs_state']="Sin Resolver";
        }
        $pqrs->responsePqrs;
        $pqrs->rol;

        array_push($info, $pqrs);

        return $pqrs;
    }

    //*** Crear un nuevo registro de PQRS ***//

    public function registerPQRS(Request $request) {

        //--- Registrar PQRS ---//

        $pqrs = new CvPqrs();

        $pqrs->id_card = $request->card_id;
        $pqrs->name = $request->contact_name;
        $pqrs->conservation_agreement_corporation = $request->agreement_corporation;
        $pqrs->email = $request->email;
        $pqrs->subscribe_agreement = $request->subcribe_agreement;
        $pqrs->subscribe_agreement = $request->subcribe_agreement;

        //--- Registrar un predio potencial si enviar los datos de nombre y coordenadas ---//
        if (!empty($request->property_name) && !empty($request->lat) && !empty($request->lng)) {

            $generalProperty = new GeneralPropertyController();

            $request["name"] = $request->property_name;

            $responsePotential = $generalProperty->registerPropertyPotential($request);
        }

        $pqrs->description = $request->description;

        if (isset($responsePotential["id"])) {
            $pqrs->property_id = $responsePotential["id"];
        }
        $dependence=CvRolePqrs::where('dependencies_role_id',$request->role_id);
        if ($dependence->exists()){
            $deprole=$dependence->first()->id;
        }else{
            return [
                "message" => "El rol no puede atender pqrs",
                "response_code" => 500
            ];
        }

        $pqrs->dependencies_role_id = $deprole;
        $pqrs->type_pqrs_id = $request->type_pqrs;

        if ($pqrs->save()) {
            return [
                "message" => "Registro exitoso",
                "response_code" => 200
            ];
        } else {
            return [
                "message" => "Se presento un error en el registro",
                "response_code" => 500
            ];
        }
    }

    //*** Actualizar registro de PQRS ***//

    public function updatePQRS(Request $request, $id) {

        $pqrs = CvPqrs::find($id);

        if (empty($pqrs)) {
            return [
                "message" => "La solicitud pqrs no existe en el sistema",
                "response_code" => 200
            ];
        }

        $pqrs->dependencies_role_id = $request->role_id;
        $pqrs->type_pqrs_id = $request->type_pqrs;

        if ($pqrs->save()) {
            return [
                "message" => "Registro actualizado",
                "response_code" => 200
            ];
        }
    }

    //*** Consultar tipos de PQRS ***//
    public function consultTypePQRS() {

        return CvTypePqrs::orderBy('name', 'ASC')->get();
    }

    //*** Respuesta para un PQRS ***//

    public function responsePQRS(Request $request) {

        //--- Validar si existe una respuesta a un PQRS ---//
        $existResponsePQRS = CvResponsePqrs::where("pqrs_id", $request->pqrs_id)->exists();

        if ($existResponsePQRS == true) {
            return [
                "message" => "Ya existe una respuesta para el PQRS",
                "response_code" => 200
            ];
        }

        $responsePQRS = new CvResponsePqrs();

        $responsePQRS->response_email_request_pqrs = $request->response;
        $responsePQRS->pqrs_id = $request->pqrs_id;

        //--- Enviar correo electronico al usuario que genero solicito el PQRS ---//

        $emailController = new GeneralEmailController();

        $pqrs = CvPqrs::find($request->pqrs_id);
        $pqrs->state = 1;
        $pqrs->save();

        //--- Parametros para la funcion email ---//

        $view = "emails.pqrs";

        $info = array(
            "email" => $pqrs->email,
            "subject" => "Respuesta de solicitud PQRS",
            "description" => $responsePQRS->response_email_request_pqrs
        );

        if ($responsePQRS->save()) {

            $emailController->sendEmail($view, $info);

            return [
                "message" => "Registro exitoso",
                "response_code" => 200
            ];
        }
    }

}
