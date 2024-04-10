<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\General\GeneralEmailController;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\CvQuota;
use App\CvContractor;
use App\CvCategory;
use App\CvCategoryByContractor;
use App\Http\Controllers\Search\SearchAlgoliaController;
use Illuminate\Support\Facades\Mail;
use Psy\Util\Str;

class UserController extends Controller {

    //*** Consultar todos los usuarios registrados ***//

    public function consultAllUsers() {

        $users = User::get();

        $info = array();

        foreach ($users as $user) {
            $user->contractor;
            array_push($info, $user);
        }

        return $info;
    }

    //*** Consultar todos los usuarios registrados por un rol en especifico ***//

    public function consultAllUsersByRol($id) {

        $usersByRol = User::where("role_id", $id)->get();

        $info = array();

        foreach ($usersByRol as $user) {
            $user->contractor;
            array_push($info, $user);
        }

        return $info;
    }

    //*** Consultar usuario en especifico ***//

    public function consultUserSpecific($id) {

        $user = User::find($id);

        $categories = [];

        if (isset($user->contractor)) {
            $user->contractor;
            foreach ($user->category as $value) {
                array_push($categories, $value->id . '');
            }
        }
        $user["categories"] = $categories;
        unset($user["category"]);

        return $user;
    }

    //*** Registrar usuario ***//

    public function registerUser(UserRequest $request) {
        $strPassword = \Illuminate\Support\Str::random(8);
        try {

            //--- Instancia del modelo de usuario ---//

            $user = new User();

            $user->names = $request->user["names"];
            $user->last_names = $request->user["last_names"];
            $user->email = $request->user["email"];
            $user->name = $request->user["name"];
            $user->password = bcrypt($strPassword);

            $user->role_id = $request->user["rol_id"];

            //--- Validar si el usuario es guarda cuenca para registrar una cuota ---//

            if ($user->save()) {

                switch ($user->role_id) {

                    case 4:
                        $quota = new CvQuota();
                        $quota->quota = $request->quota;
                        $quota->user_id = $user->id;

                        if ($this->infoSearchUser($user->id) == true) {

                            return [
                                "message" => "Usuario registrado",
                                "response_code" => 200
                            ];
                        }
                        break;
                    case 5:

                        $contractor = new CvContractor();

                        /*$contractor->contract_number = $request->contract_number;*/
                        $contractor->contract_number = $request->contract_number;
                        $contractor->type_person = $request->type_person;

                        /*$contractor->type_indentity = $request->type_indentity;*/

                        $contractor->number_identity = $request->number_identity;
                        $contractor->object = $request->object;
                        $contractor->total_value = $request->total_value;
                        $contractor->way_to_pay = $request->way_to_pay;
                        $contractor->monthly_value = $request->monthly_value;
                        $contractor->place_of_execution = $request->place_of_execution;
                        $contractor->initial_term = $request->initial_term;
                        $contractor->final_term = $request->final_term;
                        $contractor->subscription_date = $request->subscription_date;
                        $contractor->start_date = $request->start_date;
                        $contractor->termination_date = $request->termination_date;
                        $contractor->renew_guarantee = $request->renew_guarantee; //Boolean 1 yes 0 no
                        $contractor->guarantee = $request->guarantee;
                        $contractor->number_modality = $request->number_modality;
                        $contractor->user_from_id = $this->userLoggedInId();

                        //--- Foreign key ---//

                        $contractor->user_id = $user->id;
                        $contractor->contract_modality_id = $request->contract_modality; // 1 Contratación directa 2 Convocatoria pública número 3 Invitación a cotizar
                        $contractor->type_contract_id = $request->type_contract; // 1 Prestación de servicios 2Orden de servicios 3Orden de compra 4 Contrato de Transporte

                        if ($contractor->save()) {

                            for ($i = 0; $i < count($request->categories); $i++) {
                                $categori = new CvCategoryByContractor();
                                $categori->users_id = $user->id;
                                $categori->categories_id = (int) $request->categories[$i];
                                $categori->save();
                            }

                            //--- Parametros para la funcion email ---//
                            $view = "emails.task_assigned";
                            $infoEmail = array(
                                "email" =>  $request->user["email"],
                                "subject" => "Registro de nuevo contratista",
                                "title" => "Registro de nuevo contratista",
                                "type" => "Estimado ".$request->user["names"]." ". $request->user["last_names"],
                                "description" => "Te han registrado  como contratista ingresa con el correo ".$request->user["email"]." y la siguiente contraseña: ".$strPassword
                            );
                            $emailController = new GeneralEmailController();
                            $emailController->sendEmail($view, $infoEmail);
                            //END SEND EMAIL
                            if ($this->infoSearchUser($user->id) == true) {
                                return [
                                    "message" => "Registro exitoso",
                                    "response_code" => 200,
                                    "object_id" => $user->id
                                ];
                            }
                        }
                        break;
                }


                if ($this->infoSearchUser($user->id) == true) {
                    return [
                        "message" => "Registro exitoso",
                        "response_code" => 200
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                "message" => "Se ha presentado un error: " . $e->getMessage() . "\n",
                "response_code" => 500
            ];
        }
    }

    //*** Consultar valor de cuota cuando el usuario es guarda cuenca ***//

    public function consultUserGuardQuota($id) {

        $userQuota = CvQuota::where("user_id", $id)->first();

        if (!empty($userQuota)) {
            return $userQuota;
        }
    }

    //*** Actualizar cuota de usuario guarda cuenca ***//

    public function updateUserGuardQuota(Request $request, $id) {

        $userQuota = CvQuota::where("user_id", $id)->first();

        if (!empty($userQuota)) {

            $userQuota->quota = $request->user_quota;

            if ($userQuota->save()) {
                return "Cuota actualizada";
            }
        } else {
            $userQuota = new CvQuota();

            $userQuota->quota = $request->user_quota;
            $userQuota->user_id = $id;

            if ($userQuota->save()) {
                return "Cuota actualizada";
            }
        }
    }

    //*** Actualizar usuario ***//

    public function updateUser(Request $request, $id) {

        try {

            $user = User::find($id);

            if (empty($user)) {
                return [
                    "message" => "El usuario no existe en el sistema",
                    "response_code" => 200
                ];
            }

            $user->names = $request->user["names"];
            $user->last_names = $request->user["last_names"];
            $user->email = $request->user["email"];
            $user->name = $request->user["name"];
            if (isset($request->user["pass"])) {
                $user->password = bcrypt($request->user["pass"]);
            }

            $user->role_id = $request->user["role_id"];

            //--- Validar si el usuario es guarda cuenca para registrar una cuota ---//

            if ($user->save()) {

                switch ($user->role_id) {

                    case 4:

                        $quota = CvQuota::where("user_id" . $user->id)->first();
                        $quota->value = $request->quota;
                        $quota->user_id = $user->id;

                        if ($quota->save()) {

                            if ($this->infoSearchUser($user->id) == true) {
                                return [
                                    "message" => "Usuario actualizado con cuota de guarda cuenca",
                                    "response_code" => 200
                                ];
                            }
                        }

                        break;

                    case 5:

                        $contractor = CvContractor::where("user_id", $user->id)->first();

                        $contractor->contract_number = $request->contract_number;
                        $contractor->type_person = $request->type_person;
                        $contractor->number_identity = $request->number_identity;
                        $contractor->object = $request->object;
                        $contractor->total_value = $request->total_value;
                        $contractor->way_to_pay = $request->way_to_pay;
                        $contractor->monthly_value = $request->monthly_value;
                        $contractor->place_of_execution = $request->place_of_execution;
                        $contractor->initial_term = $request->initial_term;
                        $contractor->final_term = $request->final_term;
                        $contractor->subscription_date = $request->subscription_date;
                        $contractor->start_date = $request->start_date;
                        $contractor->termination_date = $request->termination_date;
                        $contractor->renew_guarantee = $request->renew_guarantee;
                        $contractor->guarantee = $request->guarantee;
                        $contractor->number_modality = $request->number_modality;
                        $contractor->user_from_id = $this->userLoggedInId();

                        //--- Foreign key ---//

                        $contractor->user_id = $user->id;
                        $contractor->contract_modality_id = $request->contract_modality;
                        $contractor->type_contract_id = $request->type_contract;

                        if ($contractor->save()) {

                            $deletcategory = CvCategoryByContractor::where('users_id', $user->id)->delete();
                            for ($i = 0; $i < count($request->categories); $i++) {
                                $categori = new CvCategoryByContractor();
                                $categori->users_id = $user->id;
                                $categori->categories_id = (int) $request->categories[$i];
                                $categori->save();
                            }

                            if ($this->infoSearchUser($user->id) == true) {
                                return [
                                    "message" => "Registro actualizado",
                                    "response_code" => 200,
                                    "object_id" => $user->id
                                ];
                            }
                        }

                        break;
                }

                if ($this->infoSearchUser($user->id) == true) {
                    return [
                        "message" => "Registro actualizado",
                        "response_code" => 200
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                "message" => "Se ha presentado un error: " . $e->getMessage() . "\n",
                "response_code" => 500
            ];
        }
    }

    //*** Eliminar usuario ***//

    public function deleteUser($id) {

        $user = User::find($id);
        $user->state = 1;

        if ($user->save()) {

            return [
                "message" => "Usuario eliminado",
                "response_code" => 500
            ];
        }
    }

    //*** Filtrar informacion del usuario para el buscador ***//

    public function infoSearchUser($user_id) {

        //--- Instancia del modelo del buscador universal con algolia ---//

        $searchAlgoliaController = new SearchAlgoliaController();

        $user = User::find($user_id);
        $type = $user->role->name;

        $description = "";

        //--- Filtro por tipo de usuario ---//
        switch ($user->role_id) {

            case 4:

                $description = "Usuario" . " " . $user->name . ", " .
                        "Correo electrónico" . " " . $user->email . ", " .
                        "Cuota" . " " . $user->quota;
                break;

            case 5:

                $typePerson = ($user->contractorOne->type_person == 1) ? $user->contractorOne->type_person = "Natural" : $user->contractorOne->type_person = "Jurídico";

                $description = "Usuario" . ": " . $user->name . ", " .
                        "Correo electrónico" . ": " . $user->email . ", " .
                        "Numero de contrato" . ": " . $user->contractorOne->contract_number . ", " .
                        "Tipo de persona" . ": " . $typePerson . ", " .
                        "Número de identificación" . ": " . $user->contractorOne->number_identity . ", " .
                        "Objeto" . ": " . $user->contractorOne->object . ", " .
                        "Valor total" . ": " . $user->contractorOne->total_value . ", " .
                        "Medio de pago" . ": " . $user->contractorOne->way_to_pay . ", " .
                        "Valor mensual" . ": " . $user->contractorOne->monthly_value . ", " .
                        "Lugar de ejecución" . ": " . $user->contractorOne->place_of_execution . ", " .
                        "Número de modalidad" . ": " . $user->contractorOne->number_modality;
                break;

            default:

                $description = "Usuario" . " " . $user->name . ", " .
                        "Correo electrónico" . " " . $user->email . ", ";
                break;
        }


        $dataSearch = [
            "name" => $user->names . " " . $user->last_names,
            "description" => $description,
            "type" => $type,
            "entity_id" => $user->id
        ];

        if ($searchAlgoliaController->registerSearchUniversal($dataSearch) == 200) {
            return true;
        }
    }

}
