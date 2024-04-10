<?php

namespace App\Http\Controllers\PotentialProperty;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralFileController;
use App\CvPotentialPropertyPoll;
use App\CvPotentialLetterIntention;
use App\CvPotentialProperty;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use App\CvFile;
use App\CvFileByPotentialProperty;
use App\User;
use App\CvBackupPotentialFile;
use App\CvBackupPotentialLetterPoll;
use App\CvPotentialPropertyByFile;
use App\CvBackupFlowPotentialProperty;
use App\CvProperty;
use App\CvComment;
use App\CvPotentialByComment;
use App\CvPropertyByUser;
use App\CvProcess;
use App\CvBackupCoordinatePotentailProperty;
use App\Http\Controllers\Sketch\SketchInitController;
use App\Http\Controllers\Method\FunctionsSpecificController;
use App\Http\Controllers\General\GeneralNotificationController;

class PotentialPropertyController extends Controller {
    /*
     * Consultar predios potenciales
     */

    public function consultPropertiesPotentials($filter) {

        //--- Información del las propiedades potenciales ---//
        $info = array();

        //--- Consultar todas la tareas con predios reales y potenciales ---//
        $propertyPotentialAll = CvPotentialProperty::where('potential_sub_type_id','!=',4)->get();

        foreach ($propertyPotentialAll as $propertyPotential) {

            $dataTsk = $this->getDataTask($propertyPotential);
            $cvPotentialPropertyPoll = CvPotentialPropertyPoll::where('potential_property_id',$propertyPotential->id)->first();
            //Valida si la encuesta del predio existe.
            if($cvPotentialPropertyPoll){
                    $municipality =  '';
                    $embalse =  null;

                $arrayJson = json_decode($cvPotentialPropertyPoll->info_json_general, true);
                if (is_array($arrayJson)){
                    if (array_key_exists('municipality', $arrayJson)){
                        $municipality = $arrayJson['municipality'];
                    }
                    if (array_key_exists('property_reservoir', $arrayJson)){
                        $embalse = $arrayJson['property_reservoir'];
                    }
                }

                $poll=500;
                $letter=500;
                $archiveLoad=500;
                $validate_pool=false;
                $validate_letter=false;
                if (CvPotentialPropertyPoll::where('potential_property_id',$propertyPotential->id)->exists()){
                    $validate_pool=true;
                }
                if (CvPotentialLetterIntention::where('potential_property_id',$propertyPotential->id)->exists()){
                    $validate_letter=true;
                }
                if ($validate_pool == true && $validate_letter == true){
                    $infor_pool=json_decode(CvPotentialPropertyPoll::where('potential_property_id',$propertyPotential->id)->first()->info_json_general,true);
                    $infor_letter=json_decode(CvPotentialLetterIntention::where('potential_property_id',$propertyPotential->id)->first()->form_letter,true);

                    if (key_exists('psa',$infor_letter)){
                        $letter = $infor_letter['psa'] ? 200 : 500;
                    }else{
                        $letter=200;
                    }

                    if (key_exists('psa',$infor_pool)){
                        $poll = $infor_pool['psa'] ? 200 : 500;
                    }else{
                        $poll=200;
                    }

                    if ($poll == 200 && $letter == 200){
                        $archiveLoad=200;
                    }
                }
                if ($filter == 1){
                    if($propertyPotential->potentialPropertySubType->id == 1 &
                        ($propertyPotential->potentialPropertyByUser[0]->role->id == 9 ||
                            $propertyPotential->potentialPropertyByUser[0]->role->id == 15 ||
                            $propertyPotential->potentialPropertyByUser[0]->role->id == 4)){

                        //--- Respuesta personalizada ---//
                        array_push($info, array(
                            "id" => $propertyPotential->id,
                            "property_name" => $propertyPotential->property_name,
                            "municipality" => $municipality,
                            "main_coordinate" => $propertyPotential->main_coordinate,
                            "created_at" => $propertyPotential->created_at->format('d-m-Y h:i a'),
                            "updated_at" => $propertyPotential->updated_at->format('d-m-Y h:i a'),
                            "user_id" => $propertyPotential->potentialPropertyByUser[0]->id,
                            "subtype" => $propertyPotential->potentialPropertySubType->name,
                            "subtype_id" => $propertyPotential->potentialPropertySubType->id,
                            "archive_load" => $archiveLoad,
                            "rol_id" => $propertyPotential->potentialPropertyByUser[0]->role->id,
                            "sign" => $dataTsk['signing'],
                            "embalse" => $embalse,
                        ));
                    }
                }
                else {

                    $returnData = false;
                    foreach ($propertyPotential->potentialPropertyByUser as $item) {
                        if ($item->id == $this->userLoggedInId()) {
                            $returnData = true;
                        }
                    }

                    if ($returnData) {
                        //--- Respuesta personalizada ---//
                        array_push($info, array(
                            "id" => $propertyPotential->id,
                            "property_name" => $propertyPotential->property_name,
                            "municipality" => $municipality,
                            "main_coordinate" => $propertyPotential->main_coordinate,
                            "created_at" => $propertyPotential->created_at->format('d-m-Y h:i a'),
                            "updated_at" => $propertyPotential->updated_at->format('d-m-Y h:i a'),
                            "user_id" => $propertyPotential->potentialPropertyByUser[0]->id,
                            "subtype" => $propertyPotential->potentialPropertySubType->name,
                            "subtype_id" => $propertyPotential->potentialPropertySubType->id,
                            "archive_load" => $archiveLoad,
                            "rol_id" => $propertyPotential->potentialPropertyByUser[0]->role->id,
                            "sign" => $dataTsk['signing'],
                            "embalse" => $embalse,
                        ));
                    }
                }

            }
        }

        return $info;
    }

    /*
     * Registrar informacion de todos los documentos encuensta y carta de intencion
     */

    public function registerPotential(Request $request) {

        //--- Consultar predio potencial ---//
        $propertyPotentialExists = CvPotentialProperty::find($request->potential_id);

        if (empty($propertyPotentialExists)) 
        {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        if (count($propertyPotentialExists->potentialPropertyByUser) == 1 ) {
            //$propertyPotentialExists->potentialPropertyByUser[0]->id == $this->userLoggedInId()
            //se quito del if anterior

            //--- Validar si aun no existe una encuesta ---//
            $poll = ($propertyPotentialExists->potentialPropertyPoll == null) ? $this->potentialPropertyPoll($request, true, $propertyPotentialExists->potential_sub_type_id) : $this->potentialPropertyPoll($request, false, $propertyPotentialExists->potential_sub_type_id);

            //--- Validar si aun no existe una carta de intencion ---//
            $letter = ($propertyPotentialExists->potentialLetterIntention == null) ? $this->potentialPropertyLetterIntention($request, true, $propertyPotentialExists->potential_sub_type_id) : $this->potentialPropertyLetterIntention($request, false, $propertyPotentialExists->potential_sub_type_id);

            $validate_pool=false;
            $validate_letter=false;

            if (CvPotentialPropertyPoll::where('potential_property_id',$request->potential_id)->exists()){
                $validate_pool=true;
            }


            if (CvPotentialLetterIntention::where('potential_property_id',$request->potential_id)->exists()){
                $validate_letter=true;
            }


            if ($validate_pool == true && $validate_letter == true){
                $infor_pool=json_decode(CvPotentialPropertyPoll::where('potential_property_id',$request->potential_id)->first()->info_json_general,true);
                $infor_letter =json_decode(CvPotentialLetterIntention::where('potential_property_id',$request->potential_id)->first()->form_letter,true);

                if (key_exists('psa',$infor_letter)){
                    $letter = $infor_letter['psa'] ? 200 : 500;
                }else{
                    $letter=200;
                }

                if (key_exists('psa',$infor_pool)){
                    $poll = $infor_pool['psa'] ? 200 : 500;
                }else{
                    $poll=200;
                }
            }

            if ($poll == 200 && $letter == 200){
                $user = CvPropertyByUser::where('property_id',$request->potential_id);
                if ($user->count() <= 1){
                    $user=$user->first();

                    $user->user_id =User::where('role_id',9)->inRandomOrder()->first()->id;
                    $propertyPotentialExists->potential_sub_type_id=7;

                    $user->save();

                }
            }else{
                $propertyPotentialExists->potential_sub_type_id = 1;
            }

            $arrayFiles = $request->file('files');

            $comments = json_decode($request->comments, true);

            if (is_array($arrayFiles) && !empty($arrayFiles)) {
                foreach ($arrayFiles as $file) {
                    $id_file_save = $this->saveFilePotentialProperty($file, $request->potential_id, $request->type);
                    if (!empty($comments)) {
                        foreach ($comments as $key => $value) {
                            if ($file->getClientOriginalName() == $key) {
                                $data_file = CvFileByPotentialProperty::find($id_file_save);
                                $data_file->description = $value;
                                $data_file->save();
                            }
                        }
                    }
                }
            }

            $propertyPotentialExists->save();
            return [
                "message" => "Registro exitoso",
                "code" => 200,
                "property_id" => $propertyPotentialExists->id,
                "response" => [
                    "poll" => $poll,
                    "letter" => $letter
                ]
            ];
        } else {

            return[
                "message" => "El predio potencial esta relacionado a mas de una usuario",
                "code" => 500
            ];
        }
    }

    public function saveFilePotentialProperty($file, $potential_id, $type) {
        $generalFileController = new GeneralFileController();
        $ffile = $file;
        $ffile = str_replace('/tmp/', '', $ffile);
        $filename = sha1(time() . $ffile);
        $extension = $file->getClientOriginalExtension();
        $nameFile = $filename . '_' . $file->getClientOriginalName();// . '.' . $extension;

        if ($extension == "png" || $extension == "jpg" || $extension == "jpeg" || $extension == "JPEG" || $extension == "JPG" || $extension == "PNG") {
            $generalFileController->storageImage($nameFile, $file);
        } else {
            Storage::disk('local')->put('documents/' . $nameFile, File::get($file));
        }

        $file_potential = new CvFileByPotentialProperty();
        $file_potential->name = $nameFile;
        $file_potential->potential_id = $potential_id;
        $file_potential->state_delete = 0;
        $file_potential->type = $type;
        $file_potential->save();
        return $file_potential->id;
    }

    /*
     * Registrar encuesta
     */

    public function potentialPropertyPoll($request, $option, $subType) {

        if ($subType == 1 || $subType == 2 || $subType == 3 || $subType == 4 || $subType == 5 || $subType == 6) {

            //venus
            $info_general = json_decode($request->info_general);
            $recUserId = User::where('email', $info_general->receiver_email)->first()->id;
            $generalNotificationController = new GeneralNotificationController();
            $generalNotificationController->notificationFlowPotential($request->potential_id, $recUserId);
            $generalNotificationController->MailPotentialApprove($request->potential_id, $recUserId);
            ///////////////////

            $potentialProperty = ($option == true) ? new CvPotentialPropertyPoll() : CvPotentialPropertyPoll::where("potential_property_id", $request->potential_id)->first();
            $potentialProperty->info_json_general = $request->info_general;
            $potentialProperty->potential_property_id = $request->potential_id;

            if ($potentialProperty->info_json_general != null) {
                if ($potentialProperty->save()) {
                    return 200;
                } else {
                    return null;
                }
            }
        } else {
            return 500;
        }
    }

    /*
     * Registrar carta de intension
     */

    public function potentialPropertyLetterIntention($request, $option, $subType) {

        if ($subType == 1 || $subType == 2 || $subType == 3 || $subType == 4 || $subType == 5 || $subType == 6) {

            $potentialLetterIntention = ($option == true) ? new CvPotentialLetterIntention() : CvPotentialLetterIntention::where("potential_property_id", $request->potential_id)->first();
            //venus
            $form_letter = json_decode($request->form_letter);
            $recUserId = User::where('email', $form_letter->receiver_email)->first()->id;
            $generalNotificationController = new GeneralNotificationController();
            $generalNotificationController->notificationFlowPotential($request->potential_id, $recUserId);
            $generalNotificationController->MailPotentialApprove($request->potential_id, $recUserId);
            ///////////////////
            $potentialLetterIntention->form_letter = $request->form_letter;
            $potentialLetterIntention->user_id = $this->userLoggedInId();
            $potentialLetterIntention->potential_property_id = $request->potential_id;

            if ($potentialLetterIntention->form_letter != null) {
                if ($potentialLetterIntention->save()) {
                    return 200;
                } else {
                    return null;
                }
            }
        } else {
            return 500;
        }
    }

    /*
     * Detalle del predio potencial
     */

    public function detailPotentialProperty($id) {

        $municipality = '';
        $embalse = null;

        $detailPotentialProperty = CvPotentialProperty::find($id);

        if (empty($detailPotentialProperty)) {

            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Validar si el predio potencial ya cuenta con los documentos ---//

        $arrayPoll = json_decode($detailPotentialProperty->potentialPropertyPoll->info_json_general, true);

        if (array_key_exists('contact', $arrayPoll))
            if (array_key_exists('contact_email', $arrayPoll['contact']))
                $existDetailPotentialPoll = true;
            else
                $existDetailPotentialPoll = false;
        else
            $existDetailPotentialPoll = false;

        if (is_array($arrayPoll)){
            if (array_key_exists('municipality', $arrayPoll)){
                $municipality = $arrayPoll['municipality'];
            }
            if (array_key_exists('property_reservoir', $arrayPoll)){
                    $embalse = $arrayPoll['property_reservoir'];
            }
        }


        $arrayLetterIntention = json_decode($detailPotentialProperty->potentialLetterIntention->form_letter, true);

        if(array_key_exists('email',$arrayLetterIntention))
            $existDetailPotentialLetter = true;
        else
            $existDetailPotentialLetter = false;


        $existPotentialFile = $detailPotentialProperty->potentailPropertyByFilePivot;

        //--- Validar si existe minimo una cedula de ciudania y certificado de tradicion ---//

        $existDocumentCC = 0;
        $existDocumentCT = 0;

        //--- Clasificar y obtener los documentos del predio potencial ---//

        $documentsCC = array();
        $documentsCT = array();

        if (is_object($existPotentialFile) && !empty($existPotentialFile)) {
            foreach ($existPotentialFile as $valueExistPotentialFile) {

                //--- Contador y almacenar archivos  ---//

                $existDocumentCC = ($valueExistPotentialFile->type_file == "cc") ? $existDocumentCC + 1 : $existDocumentCC + 0;
                $existDocumentCT = ($valueExistPotentialFile->type_file == "ct") ? $existDocumentCT + 1 : $existDocumentCT + 0;

                if ($valueExistPotentialFile->type_file == "cc") {
                    array_push($documentsCC, CvFile::find($valueExistPotentialFile->file_id));
                }

                if ($valueExistPotentialFile->type_file == "ct") {
                    array_push($documentsCT, CvFile::find($valueExistPotentialFile->file_id));
                }
            }
        }

        //--- Validar respuesta de si existe los cuatro documentos ---//

        $infoDocumentsState = [
            "poll" => [
                "state" => $existDetailPotentialPoll,
                "info" => $detailPotentialProperty->potentialPropertyPoll
            ],
            "letter" => [
                "state" => $existDetailPotentialLetter,
                "info" => $detailPotentialProperty->potentialLetterIntention
            ],
            "cc" => [
                "state" => $existDocumentCC,
                "info" => $documentsCC
            ],
            "ct" => [
                "state" => $existDocumentCT,
                "info" => $documentsCT
            ],
        ];

        //--- Actualizar la informacion del predio potencial si cuenta con todos los documentos ---//

        if ($existDetailPotentialPoll == TRUE && $existDetailPotentialLetter == TRUE && $existDocumentCC > 0 && $existDocumentCT > 0) {

            $detailPotentialProperty->check_state = TRUE;
            $detailPotentialProperty->save();
        } else {

            $detailPotentialProperty->check_state = FALSE;
            $detailPotentialProperty->save();
        }

        $tasksData = $this->getDataTask($detailPotentialProperty);
        //--- Personalizar la respuesta ---//

        return $infoResponse = [
            "id" => $detailPotentialProperty->id,
            "property_name" => $detailPotentialProperty->property_name,
            "main_coordinate" => $detailPotentialProperty->main_coordinate,
            "sub_type" => $detailPotentialProperty->potential_sub_type_id,
            "sub_type_name" => $detailPotentialProperty->potentialPropertySubType->name,
            "check_state" => $detailPotentialProperty->check_state,
            "potential_assigned" => (count($detailPotentialProperty->potentialPropertyByUser) == 1) ? TRUE : FALSE,
            "created_at" => $detailPotentialProperty->created_at->format('d-m-Y h:i a'),
            "updated_at" => $detailPotentialProperty->updated_at->format('d-m-Y h:i a'),
            "load_documents" => $infoDocumentsState,
            "comments" => $detailPotentialProperty->potentialPropertyByComment,
            'sign' => $tasksData['signing'],
            'municipality' => $municipality,
            'embalse' => $embalse,
        ];
    }

    /*
     *  Registrar comentario 
     */

    public function propertyPotentialByComment(Request $request) {

        $potentialProperty = CvPotentialProperty::find((int) $request->potential_id);

        if (empty($potentialProperty)) {

            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }


        //--- Instaciar el modelo comentario ---//

        if (!empty($potentialProperty)) {

            $comment = new CvComment();

            $comment->description = $request->comment;

            if ($comment->save()) {

                $commentByPotential = new CvPotentialByComment();

                $commentByPotential->comment_id = $comment->id;
                $commentByPotential->potential_id = $request->potential_id;
                $commentByPotential->user_id = $this->userLoggedInId();
                $commentByPotential->potential_sub_type_id = $request->sub_type;

                if ($commentByPotential->save()) {
                    return [
                        "message" => "Registro exitoso",
                        "response_code" => 200
                    ];
                }
            }
        }
    }

    /*
     * Flujo de los sub tipos del predio potencial
     */

    public function approvedPotentialProperty(Request $request) {

        //--- Valirdar y realizar los cambios al predio potencial ---//
        $potentialProperty = CvPotentialProperty::find($request->potential_id);

        //--- venus add
        $generalNotificationController = new GeneralNotificationController();
        $generalNotificationController->notificationFlowPotential($request->potential_id, $request->recUserId);
        $generalNotificationController->MailPotentialApprove($request->potential_id, $request->recUserId);
        ////////////////////////

        if (empty($potentialProperty)) {

            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Validar que el/los usuario(s) logueado se encuentre vinculado al predio potencial ---//
        $messageError = [
            "message" => "El usuario autenticado no esta vinculado al predio potencial, lo cual no puede realizar esta accion",
            "code" => 500
        ];

        $validateApproved = TRUE;

        if ($potentialProperty->potentialPropertyByUser != null) {

            if ($this->userLoggedInId() != $potentialProperty->potentialPropertyByUser[0]->id) {

                switch ($this->userLoggedInRol()) {
                    case 3:
                    case 9:
                    case 15:
                    case 16:
                    case 10:

                        $potentialProperty->potentialPropertyByUser;

                        if (count($potentialProperty->potentialPropertyByUser) > 0) {

                            foreach ($potentialProperty->potentialPropertyByUser as $potentialPropertyUser) {
                                switch ($this->userLoggedInRol()) {
                                    case 3:
                                    case 9:
                                    case 15:
                                    case 16:
                                    case 10:
                                        $validateApproved = TRUE;
                                        break;
                                    default :
                                        $validateApproved = ($this->userLoggedInRol() == $potentialPropertyUser->role_id) ? TRUE : FALSE;
                                        break;
                                }
                            }
                        }

                        break;

                    default:

                        $validateApproved = FALSE;
                        return $messageError;
                }
            }
        }

        if ($validateApproved == TRUE) {

            //--- Validar que se encuentren los documentos de un predio potencial ---//
            $getSubType = $this->getSubTypePotentialProperty($potentialProperty->potential_sub_type_id);

            if ($getSubType["sub_type"] != null) {

                $potentialProperty->potential_sub_type_id = $getSubType["sub_type"];

                //--- Asignar el predio potencial a los usuario de un determinado rol ---//
                $roles = $this->rolesUsersForPotentialProperty($getSubType["role"], $potentialProperty->id);

                $infoRoles = implode(",", $roles);

                if ($roles != 0 && is_array($roles)) {

                    //--- Guardar los cambios del sub tipo ---//
                    $potentialProperty->save();

                    //--- Guardar la informacion en el historial ---//
                    if ($this->saveHistoryPotentialProperty($this->userLoggedInId(), $infoRoles, $potentialProperty->id) == 200) {

                        //--- Enviar notificacion One Signal y email ---//
                        foreach ($roles as $idUser) {
                            $generalNotificationController->notificationFlowPotential($potentialProperty->id, $idUser);
                            $generalNotificationController->MailPotentialApprove($potentialProperty->id, $idUser);
                        }

                        return [
                            "message" => "Envio exitoso",
                            "code" => 200
                        ];
                    }
                }
            } else {
                return [
                    "message" => "El predio potencial no cuenta con todos los documentos solicitados",
                    "code" => 500
                ];
            }
        } else {
            return $messageError;
        }
    }

    /*
     * Obtener el nuevo sub tipo
     */

    public function getSubTypePotentialProperty($subType) {

        $returnSubType = null;
        $returnRoleSubType = null;

        switch ($subType) {

            case 1:
                $returnSubType = 2;
                $returnRoleSubType = "administrative";
                break;

            case 7:
                $returnSubType = 5;
                $returnRoleSubType = "administrative";
                break;

            case 2:
                $returnSubType = 3;
                $returnRoleSubType = "legal";
                break;

            case 5:
                $returnSubType = 6;
                $returnRoleSubType = "legal";
                break;
        }

        //*** Asignar el predio potencial a los nuevos usuarios ***//
        return [
            "sub_type" => $returnSubType,
            "role" => $returnRoleSubType
        ];
    }

    /*
     * Control para determinar a que roles le quedara asignada el predio potencial
     */

    public function rolesUsersForPotentialProperty($option, $potentialId) {

        $role = 0;

        switch ($option) {

            case "administrative":

                $role = 2;
                break;

            case "legal":

                $role = 8;
                break;

            case "coordinatorGuard":

                $role = 3;
                break;
        }

        if ($role != 0) {

            //--- Buscar los usuarios con el rol indicado ---//

            $usersRole = User::where("role_id", $role)->get();

            //--- Eliminar al usuario que contiene la tarea ---//

            $potentialPropertyByUser = CvPropertyByUser::where("property_id", $potentialId)->get();

            foreach ($potentialPropertyByUser as $valuePotentialProperty) {
                $valuePotentialProperty->delete();
            }

            //--- Registrar nuevos usuarios con el rol del flujo indicado ---//

            $rolesId = array();

            foreach ($usersRole as $valueUsersRole) {

                array_push($rolesId, $valueUsersRole->id);

                $newPotentialPropertyByUser = new CvPropertyByUser();

                $newPotentialPropertyByUser->property_id = $potentialId;
                $newPotentialPropertyByUser->user_id = $valueUsersRole->id;

                $newPotentialPropertyByUser->save();
            }

            return $rolesId;
        }
    }

    /*
     * Guardar el historial del flujo del predio potencial
     */

    public function saveHistoryPotentialProperty($user_from, $user_to, $potential_id) {

        $historyPotentialProperty = new CvBackupFlowPotentialProperty();

        $historyPotentialProperty->user_from = $user_from;
        $historyPotentialProperty->user_to = $user_to;
        $historyPotentialProperty->potential_id = $potential_id;

        if ($historyPotentialProperty->save()) {
            return 200;
        }
    }

    /*
     * Guardar el usuario que seleccion el predio potencial
     */

    public function getSelectOfUserPotentialProperty($potential_id) {

        $propertyPotential = CvPropertyByUser::where("property_id", $potential_id)->get();

        if (empty($propertyPotential)) {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Variable para determinar si el usuario existe en la lista del predio potencial ---//
        $userTemp = 0;

        foreach ($propertyPotential as $valuePropertyPotential) {

            if ($valuePropertyPotential->user_id == $this->userLoggedInId()) {
                $userTemp = $valuePropertyPotential->id;
            }
        }

        //--- Eliminar todas las relaciones del predio potencial a los usuarios de un determinado rol ---//
        if ($userTemp > 0) {
            foreach ($propertyPotential as $valuePropertyPotential) {
                $valuePropertyPotential->delete();
            }
        } else {
            return [
                "message" => "El usuario no cuenta con el predio potencial asignado, lo cual no puede realizar esta acción",
                "code" => 500
            ];
        }

        //--- Asignar el predio potencial al usuarios que la selecciono ---//
        $propertyPotentialNew = new CvPropertyByUser();

        $propertyPotentialNew->property_id = $potential_id;
        $propertyPotentialNew->user_id = $this->userLoggedInId();

        if ($propertyPotentialNew->save()) {

            //--- Cambiar el sub tipo de acuerdo al rol del usuario ---//
            $potential = CvPotentialProperty::find($propertyPotentialNew->property_id);

            //--- Validar el rol del usuario ---//
            switch ($this->userLoggedInRol()) {
                case 2:
                    $potential->potential_sub_type_id = 2;
                    break;
                case 8:
                    $potential->potential_sub_type_id = 3;
                    break;
            }

            $potential->save();

            return [
                "message" => "Registro exitoso",
                "code" => 200
            ];
        }
    }

    /*
     * Retornar el flujo del predio potencial
     */

    public function backPotentialPropertyUser($potential_id) {

        $historyPotentialProperty = CvBackupFlowPotentialProperty::where("potential_id", $potential_id)->orderBy('id', 'DESC')->first();

        if (empty($historyPotentialProperty)) {

            return [
                "message" => "El predio potencial no cuenta con historial",
                "code" => 500
            ];
        }

        //--- Convertir el string en arreglo ---//

        $usersOfPotentialProperty = explode(",", $historyPotentialProperty->user_to);

        $userCurrent = 0;

        foreach ($usersOfPotentialProperty as $valueUsersOfPotentialProperty) {

            if ($this->userLoggedInId() == $valueUsersOfPotentialProperty) {
                $userCurrent = $valueUsersOfPotentialProperty;
            }
        }

        //--- Validar que el usuario autenticado corresponda al usuario que actualmente contiene el predio potencial ---//

        if ($userCurrent > 0) {

            if ($this->saveHistoryPotentialProperty($this->userLoggedInId(), $historyPotentialProperty->user_from, $potential_id) == 200) {

                $potentialPropertyByUser = CvPropertyByUser::where("user_id", $userCurrent)->get();

                //--- Eliminar la relacion del usuario actual ---//
                foreach ($potentialPropertyByUser as $valuePotentialPropertyByUser) {
                    $valuePotentialPropertyByUser->delete();
                }

                //--- Actualizar el subtipo del predio potencial ---//
                $potentialProperty = CvPotentialProperty::find($potential_id);

                $potentialProperty->potential_sub_type_id = $this->backChangePotentialProperty($potentialProperty->potential_sub_type_id);

                $potentialProperty->save();

                //--- Reasignar el predio potencial al usuarios que la selecciono ---//
                $propertyPotentialNew = new CvPropertyByUser();

                $propertyPotentialNew->property_id = $potential_id;
                $propertyPotentialNew->user_id = $historyPotentialProperty->user_from;

                if ($propertyPotentialNew->save()) {
                    return [
                        "message" => "Predio potencial reasingado",
                        "code" => 200
                    ];
                }
            }
        } else {

            return [
                "message" => "El usuario no se encuentra relacionado al predio potencial en el ultimo registro del historial",
                "code" => 500
            ];
        }
    }

    //--- Cambiar sub tipo si se regresa el predio potencial ---//

    public function backChangePotentialProperty($potentialSubType) {

        $subType = 0;

        switch ($potentialSubType) {

            case 2:
                $subType = 7;
                break;
            case 3:
                $subType = 5;
                break;
            case 5:
                $subType = 7;
                break;
            case 6:
                $subType = 5;
                break;
        }

        return $subType;
    }

    //--- Finalizar predio potencial ---//

    public function finalizedPotentialProperty($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        $functionSpecificController = new FunctionsSpecificController();

        if (!empty($potential)) {

            $functionSpecificController->updateInfoFilesPotentialPropertyRelationsProcess($potential->id);

            if (empty($potential)) {

                return [
                    "message" => "El predio potencial no existe en el sistema",
                    "code" => 500
                ];
            }

            if (empty($potential->potentailPropertyByFile) && empty($potential->potentialLetterIntention) && empty($potential->potentialPropertyPoll)) {

                return [
                    "message" => "Aún hacen falta alguno de los documentos del predio potencial",
                    "code" => 500
                ];
            }

            $potential->potential_sub_type_id = 4;

            if ($potential->save()) {
                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            }
        } else {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }
    }

    /*
     * Consultar informacion en especifico de encuesta y carta de intención
     */

    public function consultInfoSpecificOfPollAndLetterIntention($potential_id) {

        $infoPotential = CvPotentialProperty::find($potential_id);

        if (empty($infoPotential)) {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        $info = [
            "info_general" => (!empty($infoPotential->potentialPropertyPoll)) ? $infoPotential->potentialPropertyPoll->info_json_general : null,
            "form_letter" => (!empty($infoPotential->potentialLetterIntention)) ? $infoPotential->potentialLetterIntention->form_letter : null,
            "potential_id" => 1
        ];

        return $info;
    }

    /*
     * Consultar archivos del predio potencial
     */

    public function consultFilesPotentialPotential($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (empty($potential)) {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Filtrar los archivos de cedula y certificado de tradicion ---//

        $arrayDocumentsCC = array();
        $arrayDocumentsCT = array();

        foreach ($potential->potentailPropertyByFilePivot as $valuePotentialPropertyFile) {

            if ($valuePotentialPropertyFile->type_file == "cc") {
                array_push($arrayDocumentsCC, $valuePotentialPropertyFile->file_id);
            } else {
                array_push($arrayDocumentsCT, $valuePotentialPropertyFile->file_id);
            }
        }

        //--- Consultar los archivos de acuerdo al archivo ---//

        $arrayValueDocumentsCC = array();
        $arrayValueDocumentsCT = array();

        foreach ($arrayDocumentsCC as $valueDocumentCC) {

            $fileCC = CvFile::find($valueDocumentCC);
            $type = "doc";

            if (strtolower(File::extension($fileCC->name)) == "png" || strtolower(File::extension($fileCC->name)) == "jpg" || strtolower(File::extension($fileCC->name)) == "jpeg") {
                $type = "img";
            }

            $infoDocumentCC = [
                "id" => $fileCC->id,
                "name" => $fileCC->name,
                "type" => $type,
                "created_at" => $fileCC->created_at->format('d-m-Y h:i a'),
                "updated_at" => $fileCC->updated_at->format('d-m-Y h:i a')
            ];

            array_push($arrayValueDocumentsCC, $infoDocumentCC);
        }

        foreach ($arrayDocumentsCT as $valueDocumentCT) {

            $fileCT = CvFile::find($valueDocumentCT);

            $type = "doc";

            if (strtolower(File::extension($fileCT->name)) == "png" || strtolower(File::extension($fileCT->name)) == "jpg" || strtolower(File::extension($fileCT->name)) == "jpeg") {
                $type = "img";
            }

            $infoDocumentCT = [
                "id" => $fileCT->id,
                "name" => $fileCT->name,
                "type" => $type,
                "created_at" => $fileCT->created_at->format('d-m-Y h:i a'),
                "updated_at" => $fileCT->updated_at->format('d-m-Y h:i a')
            ];

            array_push($arrayValueDocumentsCT, $infoDocumentCT);
        }

        return [
            "cc" => $arrayValueDocumentsCC,
            "ct" => $arrayValueDocumentsCT
        ];
    }

    //*** Consultar predios potenciales aprobados ***//
    public function consultPotentialProperyApproved() {

        $potentialProperty = CvPotentialProperty::where("potential_sub_type_id", 4)->get();

        if (empty($potentialProperty)) {
            return [
                "message" => "No se encuentra predios potenciales aprobados",
                "code" => 500
            ];
        }

        return $potentialProperty;
    }

    //*** Actualizar coordenada de un predio potencial ***//
    public function updateCoordinatePotentialProperty(Request $request) {

        $potentialProperty = CvPotentialProperty::find($request->potential_id);

        $potentialPropertyPoll = CvPotentialPropertyPoll::find($potentialProperty->potentialPropertyPoll->id);

        $potentialPropertyInfoGeneralJson = json_decode($potentialPropertyPoll->info_json_general, TRUE);
        $potentialPropertyInfoGeneralJson["economic_activity_in_the_property"]["latitude"] = $request->latitude;
        $potentialPropertyInfoGeneralJson["economic_activity_in_the_property"]["longitude"] = $request->longitude;
        $potentialPropertyPoll->info_json_general = json_encode($potentialPropertyInfoGeneralJson);

        //--- Obtener la coordenada antes de ser editada ---//
        $coordinateLast = $potentialProperty->main_coordinate;

        if (empty($potentialProperty)) {

            return[
                "message" => "El predio potencial no existe en sistema",
                "code" => 500
            ];
        }

        //--- Actualizar la coordenadas de un predio potencial ---//
        $potentialProperty->main_coordinate = $request->latitude . "," . $request->longitude;
        $potentialProperty->save();

        //--- Consultar los procedimientos que contiene el predio potencial seleccionado ---//
        $process = CvProcess::where("potential_property_id", $request->potential_id)->get();

        if (empty($process)) {
            return [
                "message" => "No hay procedimientos relacionados al predio potencial",
                "code" => 500
            ];
        }

        //--- Capturar los croquis que presente error ---//
        $errorSketch = array();

        //--- Saber si se actualizo como minimo un procedimiento ---//
        $validateMinProcess = array();

        //--- Obtener la tarea de encuesta de cada procedimiento ---//
        foreach ($process as $valueProcess) {
            if (isset($valueProcess->processByTasks)) {

                //--- Validar que la tarea de medicion no se encuentre en el ultimo subtipo del flujo ---//
                $validateSubTypeTaskMap = TRUE;

                foreach ($valueProcess->processByTasks as $valueProcessTaskMap) {
                    if ($valueProcessTaskMap->task_type_id == 1 && $valueProcessTaskMap->task_sub_type_id == 33) {
                        $validateSubTypeTaskMap = FALSE;
                        array_push($validateMinProcess, 0);
                    }
                }

                if ($validateSubTypeTaskMap == TRUE) {

                    foreach ($valueProcess->processByTasks as $valueProcessTask) {

                        //--- Validar tarea de encuesta ---//
                        if ($valueProcessTask->task_type_id == 3) {

                            //--- Obtener la encuesta ---//
                            if (isset($valueProcessTask->property)) {

                                //--- Actualizar encuesta ---//

                                $propertySave = CvProperty::find($valueProcessTask->property->id);

                                $propertySave->main_coordinate = $request->latitude . "," . $request->longitude;
                                $propertyInfoGeneralJson = json_decode($propertySave->info_json_general, TRUE);
                                $propertyInfoGeneralJson["economic_activity_in_the_property"]["latitude"] = $request->latitude;
                                $propertyInfoGeneralJson["economic_activity_in_the_property"]["longitude"] = $request->longitude;
                                $propertySave->info_json_general = json_encode($propertyInfoGeneralJson);

                                //--- Actualizar croquis ---//
                                $sketchProperty = $valueProcessTask->property->properySketch;

                                $sketchController = new SketchInitController();
                                $updateSketchProperty = $sketchController->shearPointProperty($request->latitude, $request->longitude, $sketchProperty->property_id, $sketchProperty->id);

                                //--- Validar cuando se actualizo el croquis ---//

                                if ($updateSketchProperty == 200) {

                                    $propertySave->save();
                                    $potentialPropertyPoll->save();
                                    $potentialProperty->save();

                                    array_push($validateMinProcess, 1);
                                } else {
                                    array_push($errorSketch, $sketchProperty->id);
                                }
                            }
                        }
                    }
                }
            }
        }

        //--- Mostrar los croquis que no se llegaron actualizar ---//
        if (count($errorSketch) > 0) {
            return [
                "message" => "Los siguientes croquis no se pueden actualizar, por favor intentelo de nuevo",
                "date" => $errorSketch,
                "code" => 500
            ];
        } else {

            //--- Guardar el historial de la modificacion de la coordenada de un predio potencial ---//
            if (in_array(1, $validateMinProcess, true) == true) {
                $this->backupOfCoordinate($coordinateLast, $potentialProperty->id);
            }

            return [
                "message" => "Registro exitoso",
                "code" => 200
            ];
        }
    }

    //*** Historial de coordenadas ***//
    public function backupOfCoordinate($coordinate, $potential_id) {

        $backupCoordinatePotentialProperty = new CvBackupCoordinatePotentailProperty();

        $backupCoordinatePotentialProperty->coordinate = $coordinate;
        $backupCoordinatePotentialProperty->user_id_edit = $this->userLoggedInId();
        $backupCoordinatePotentialProperty->potential_property_id = $potential_id;

        if ($backupCoordinatePotentialProperty->save()) {
            return 200;
        }
    }

    //*** Eliminar archivos de predio potencial ***//

    public function deleteFilePotentialProperty(Request $request) {

        if (
            $this->userLoggedInRol() == 2 ||
            $this->userLoggedInRol() == 3 ||
            $this->userLoggedInRol() == 9 ||
            $this->userLoggedInRol() == 15 ||
            $this->userLoggedInRol() == 16 ||
            $this->userLoggedInRol() == 10
        ) {

            /*
             * Eliminar relaciones del predio potencial y tareas con los archivos del sistema ---//
             */

            //--- Predio potencial ---//
            $potentialPropertyByFile = CvPotentialPropertyByFile::where("potential_property_id", $request->potential_id)->where("file_id", $request->id_file)->get();

            if (empty($potentialPropertyByFile)) {
                return [
                    "message" => "El predio potencial no cuenta con archivos relacionados",
                    "code" => 500
                ];
            }

            $generalFileController = new GeneralFileController();
            $responseDeleteFile = $generalFileController->deleteFiles($request);

            //--- Eliminar relacion con predio potencial y archivos ---//
            if ($responseDeleteFile["code"] == 200) {

                foreach ($potentialPropertyByFile as $valuePotentialPropertyByFile) {
                    $valuePotentialPropertyByFile->delete();
                }

                //--- Guardar backup del archivo eliminado ---//
                if ($this->backupFilePotential($responseDeleteFile["name_file"], $request->potential_id) == 200) {
                    return [
                        "message" => "Eliminacion exitosa",
                        "code" => 500
                    ];
                }
            } else {
                return $responseDeleteFile;
            }
        } else {
            return [
                "response" => "El usuario no cuenta con el rol indicado para realizar esta accion",
                "code" => 500
            ];
        }
    }

    /*
     * Cambiar el subtipo del predio potencial cuando se realiza una actualizacion de los archivos y este ya ha sido aprobado
     */

    public function changeSubTypePotentialFilesPropertyApproved($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        //--- Cambiar el sub tipo ---//

        if ($potential->potential_sub_type_id == 4) {
            $potential->potential_sub_type_id = 5;
            $potential->save();
        }


        if ($potential->potential_sub_type_id > 2 && $potential->potential_sub_type_id < 7) {
            //--- Asignar predio potencial a roles administrativos ---//
            $this->assignPotentialPropertyApprovedRolesAdministrative($potential->id);
        }
    }

    /*
     * Actualizar informacion de la encuesta y carta de intencion del predio potencial si el sub tipo se encuentra en aprobacion cambiarlo a
     * verificar desde administrativo
     */

    public function updateInfoPollLetter(Request $request) {

        //--- Consultar predio potencial ---//
        $propertyPotentialExists = CvPotentialProperty::find($request->potential_id);

        if (empty($propertyPotentialExists)) {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        $poll = 0;
        $letter = 0;

        $info = "";
        $option = false;

        //--- Validar si aun no existe una encuesta ---//
        if ($propertyPotentialExists->potentialPropertyPoll != null || $propertyPotentialExists->potentialPropertyPoll != "") {
            $poll = $this->potentialPropertyPoll($request, false, $propertyPotentialExists->potential_sub_type_id);
        }

        //--- Validar si aun no existe una carta de intencion ---//
        if ($propertyPotentialExists->potentialLetterIntention != null || $propertyPotentialExists->potentialLetterIntention != "") {
            $letter = $this->potentialPropertyLetterIntention($request, false, $propertyPotentialExists->potential_sub_type_id);
        }

        if ($poll == 200 || $letter == 200) {

            switch ($propertyPotentialExists->potential_sub_type_id) {
                case 2:
                case 3:
                case 4:
                case 6:
                    $propertyPotentialExists->potential_sub_type_id = 5;
                    $propertyPotentialExists->save();
                    break;

                default:
                    break;
            }
        }

        //--- Validar el request de la informacion que es enviada para guardar la informacion en el backup
        if ($poll == 200 && $letter == null) {
            $info = $propertyPotentialExists->potentialPropertyPoll->info_json_general;
            $option = false;
        } else if ($letter == 200 && $poll == null) {
            $info = $propertyPotentialExists->potentialLetterIntention->form_letter;
            $option = true;
        }

        //--- Asignar predio potencial a roles administrativos ---//
        $this->assignPotentialPropertyApprovedRolesAdministrative($propertyPotentialExists->id);

        //--- Guardar backup de la encuesta o carta de intencion de un predio potencial ---//
        $this->backupLetterOrPoll($info, $option, $propertyPotentialExists->id);

        return [
            "message" => "Actualizacion exitosa",
            "code" => 200,
            "property_id" => $propertyPotentialExists->id,
            "response" => [
                "poll" => $poll,
                "letter" => $letter
            ]
        ];
    }

    //*** Asignar predio potencial aprobado a los usuarios con rol administrativo ***//
    public function assignPotentialPropertyApprovedRolesAdministrative($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (!empty($potential)) {
            //--- Eliminar la relacion del predio potencial con el usuario que actualmente esta relacionado ---//
            $potential->potentialPropertyByUserActual;

            $deletePotentialPropertyByUser = CvPropertyByUser::find($potential->potentialPropertyByUserActual->id);

            if ($deletePotentialPropertyByUser->delete()) {

                //--- Agregar el predio potencial a los usuarios con rol administrativo ---//

                $usersAdministrative = User::where("role_id", 2)->get();

                foreach ($usersAdministrative as $valueUser) {

                    $potentialPropertyByUser = new CvPropertyByUser();

                    $potentialPropertyByUser->property_id = $potential_id;
                    $potentialPropertyByUser->user_id = $valueUser->id;

                    $potentialPropertyByUser->save();
                }
            }
        }
    }

    //*** Consultar informacion de la encuesta de un predio potencial ***//
    public function consultPollPotentialProperty($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (empty($potential)) {
            return[
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        if (!empty($potential->potentialPropertyPoll)) {
            return json_decode($potential->potentialPropertyPoll->info_json_general, true);
        } else {
            return [
                "message" => "El predio potencial no cuenta con encuesta",
                "response_code" => 500
            ];
        }
    }

    //*** Consultar informacion de la carta de intencion de un predio potencial ***//
    public function consultLetterPotentialProperty($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (empty($potential)) {
            return[
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        if (!empty($potential->potentialLetterIntention)) {
            return json_decode($potential->potentialLetterIntention->form_letter, true);
        } else {
            return [
                "message" => "El predio potencial no cuenta con encuesta",
                "response_code" => 500
            ];
        }
    }

    //*** Guarda el log del predio potencial de los archivos ***//
    public function backupFilePotential($file, $potential_id) {
        try {
            $backfile = new CvBackupPotentialFile();
            $backfile->name = $file;
            $backfile->potential_id = $potential_id;
            $backfile->user_id = $this->userLoggedInId();
            $backfile->save();
            return 200;
        } catch (Exception $ex) {
            return 500;
        }
    }

    //*** Guarda el log de  la informacion de carta de intencion y encuesta***//
    public function backupLetterOrPoll($info, $option, $potential_id) {
        try {
            $backfile = new CvBackupPotentialLetterPoll();
            $backfile->info = $info;
            $backfile->info_letter_or_poll = $option;
            $backfile->potential_id = $potential_id;
            $backfile->user_id = $this->userLoggedInId();
            $backfile->save();
            return 200;
        } catch (Exception $ex) {
            return 500;
        }
    }

    //*** Eliminar un predio potencial ***//
    public function deletePotentialProperty($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        switch ($this->userLoggedInRol()) {
            case 2:
            case 9:
            case 15:
            case 16:
            case 10:
            case 3:
                $state = true;
                break;
            default :
                $state = false;
                return [
                    "message" => "El usuario no cuenta con permiso para realizar esta accion",
                    "code" => 500
                ];
        }

        if ($state == true) {

            if (empty($potential)) {
                return [
                    "message" => "El predio potencial no existe en el sistema.",
                    "code" => 500
                ];
            }

            //--- Validar que el predio potencial no este relacionado a uno o mas procedimientos ---//
            $processPotential = CvProcess::where("potential_property_id", $potential_id)->count();

            if ($processPotential == 0) {

                $filesIdsTemp = array();
                $filesTemp = array();

                //--- Eliminar los archivos de un predio potencial ---//
                if (!empty($potential->potentailPropertyByFile)) {

                    foreach ($potential->potentailPropertyByFile as $valuePotentialPropertyFile) {

                        array_push($filesIdsTemp, $valuePotentialPropertyFile->id);
                        array_push($filesTemp, $valuePotentialPropertyFile->name);
                    }
                }

                //--- Eliminar archivos localmente ---//
                foreach ($filesTemp as $valueFileTemp) {
                    $extensionFile = File::extension($valueFileTemp);
                    switch ($extensionFile) {
                        case "png":
                        case "jpg":
                        case "jpeg":
                            $this->deleteImages($valueFileTemp);
                            break;

                        default:
                            $this->deleteDocuments($valueFileTemp);
                            break;
                    }
                }

                //--- Eliminar en cascada el predio potencial ---//
                if ($potential->delete() == true) {

                    //--- Eliminar en la base de datos los archivos ---//
                    foreach ($filesIdsTemp as $valueFilesIdsTemp) {
                        $file = CvFile::find($valueFilesIdsTemp);
                        $file->delete();
                    }

                    return [
                        "message" => "Eliminacion exitosa",
                        "code" => 200
                    ];
                }
            } else {
                return [
                    "message" => "El predio potencial ya esta vinculado a uno o mas procedimientos",
                    "code" => 500
                ];
            }
        }
    }

    //*** Eliminar imagenes ***//
    public function deleteImages($file) {

        $existsFile = File::exists(public_path('files/images/' . $file));
        $existsFileThumbnails = File::exists(public_path('files/images/img-thumbnails/' . $file));
        $existsFileDelete = File::exists(public_path('files/imagesDelete/' . $file));

        if ($existsFile == true) {
            File::delete(public_path('files/images/' . $file));
        }

        if ($existsFileThumbnails == true) {
            File::delete(public_path('files/images/img-thumbnails/' . $file));
        }

        if ($existsFileDelete == true) {
            File::delete(public_path('files/imagesDelete/' . $file));
        }
    }

    //*** Eliminar documentos ***//
    public function deleteDocuments($file) {

        $existsFile = File::exists(public_path('files/documents/' . $file));
        $existsFileDelete = File::exists(public_path('files/documents/' . $file));

        if ($existsFile == true) {
            File::delete(public_path('files/documents/' . $file));
        }

        if ($existsFileDelete == true) {
            File::delete(public_path('files/documentsDelete/' . $file));
        }
    }

    /*
     * get data of task by CvPotentialProperty
     * */

    private function getDataTask($potentialProperty){
        $arrTask = Array();
        $arrTask['signing'] = 'N/A';

        $process = $potentialProperty->process;
        if($process) {
            $tasks = $process->processByTasks;
            foreach ($tasks as $task) {
                if($task->task_sub_type_id == 33){
                    $arrTask['signing'] = $task->updated_at->format('d-m-Y');
                }
            }
        }

        return $arrTask;
    }

    public function potentialsRealNoProcess()
    {
        $info=array();
        $potential= CvPotentialProperty::where('potential_sub_type_id',4)->get();
        foreach ($potential as $detail_potenttial){
            if (!CvProcess::where('potential_property_id',$detail_potenttial->id)->exists()){
                array_push($info,$detail_potenttial);
            }
        }
        return $info;
    }

}
