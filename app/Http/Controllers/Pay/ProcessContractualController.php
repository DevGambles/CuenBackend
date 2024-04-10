<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvPool;
use App\CvPayProcessContractor;
use App\User;
use App\Http\Controllers\General\GeneralFileController;
use App\CvFileByPayProcessContractor;
use App\Http\Controllers\General\GeneralCommentController;
use App\CvCommentByPayProcessContractor;
use App\CvFile;
use File;
use Illuminate\Support\Facades\DB;

class ProcessContractualController extends Controller {

    //--- Solicitar pago ---//
    public function requestPay(Request $request) {

        DB::beginTransaction();

        dd($request->all());

        if (!is_object($request->type)) {
            $jsonTypeFiles = json_decode($request->type, true);
        } else {
            $jsonTypeFiles = $request->type;
        }

        $pool = CvPool::find($request->pool_id);

        if (empty($pool)) {
            return[
                "message" => "La bolsa no existe en el sistema",
                "code" => 500
            ];
        }

        //==========================================================
        $generalFileController = new GeneralFileController();
        $arrayPayFiles = array();
        foreach ($request->files_pay as $requestFile) {
            dd($requestFile["file"]);
            $f = $request->file($requestFile["file"]);
            $idFile = $generalFileController->saveFilesTableBD($f->getClientOriginalName());
            array_push($arrayPayFiles, $requestFile["file"]);
        }
        $request->request->add(["files" => $arrayPayFiles]);
        $idFile = $generalFileController->saveFilesTableBD($itemFile->getClientOriginalName());
        return "success";
        //==========================================================

        $files = $request->file("files_pay");

        if (!is_array($files)) {
            return[
                "message" => "Los archivos deben ser enviados como array",
                "code" => 500
            ];
        }

        $existCvPayProcessContractor = CvPayProcessContractor::where("pool_id", $request->pool_id)->first();

        //--- Consultar todos los usuarios con el rol de financiero ---//
        $usersFinancial = User::where("role_id", 11)->select("id")->get();
        $usersFinancialId = array();

        if (empty($existCvPayProcessContractor)) {
            if (empty($files) && count($files) != 3) {
                return[
                    "message" => "Se deben agregar los tres archivos requeridos los cuales son: Parafiscales, Certificado y Factura",
                    "code" => 500
                ];
            }
        }

        foreach ($usersFinancial as $itemUser) {
            array_push($usersFinancialId, $itemUser->id);
        }

        //--- Guardar informacion del pago ---//
        if (isset($existCvPayProcessContractor->approved)) {
            if ($existCvPayProcessContractor->approved == 1) {
                return [
                    "message" => "El pago del proceso contractual ya ha sido aprovado",
                    "code" => 500
                ];
            }
        }

        $payProcessContractor = (empty($existCvPayProcessContractor)) ? new CvPayProcessContractor() : $existCvPayProcessContractor;

        if (isset($payProcessContractor->to_user)) {
            if ($this->userLoggedInId() != $payProcessContractor->to_user) {
                return [
                    "message" => "El usuario no tiene asignado el proceso de este pago",
                    "code" => 500
                ];
            }
        }

        if ($this->userLoggedInRol() != 9) {
            return [
                "message" => "El usuario no cuenta con el rol de coordinacion de apoyo de restauracion",
                "code" => 500
            ];
        }

        $countFilesPayTotalGlobal = 3;

        if (isset($existCvPayProcessContractor)) {

            $countFilesPayTotal = count($existCvPayProcessContractor->files);
            $maxTotal = 3;

            $limitCalculate = $maxTotal - $countFilesPayTotal;

            if ($limitCalculate != 0) {
                $countFilesPayTotalGlobal = $limitCalculate;
            }
        }

        $countCicleWhile = 0;

        if ($countFilesPayTotalGlobal != 0) {
            if (count($files) != $countFilesPayTotalGlobal) {
                DB::rollback();
                return[
                    "message" => "Faltan " . $countFilesPayTotalGlobal . " archivo(s) para enviar la solicitud de pago",
                    "code" => 500
                ];
            }
        }

        if ($countCicleWhile < $countFilesPayTotalGlobal) {

            $payProcessContractor->value = $request->value;
            $payProcessContractor->sub_type = (isset($payProcessContractor->sub_type) && $payProcessContractor->sub_type == 3) ? 2 : 1;
            $payProcessContractor->to_user = implode(",", $usersFinancialId);
            $payProcessContractor->from_user = $this->userLoggedInId();
            $payProcessContractor->pool_id = $request->pool_id;

            $payProcessContractor->save();

            //--- Guardar archivos ---//

            $generalFileController = new GeneralFileController();

            foreach ($files as $keyFile => $itemFile) {
                if ($generalFileController->saveFilesLocal($itemFile) != null) {
                    
                    $idFile = $generalFileController->saveFilesTableBD($itemFile->getClientOriginalName());

                    //--- Registrar archivos ---//
                    $cvFileByPayProcess = new CvFileByPayProcessContractor();
                    $cvFileByPayProcess->file_id = $idFile;
                    $cvFileByPayProcess->pay_id = $payProcessContractor->id;

                    //--- Saber el tipo de archivo ---//
                    foreach ($jsonTypeFiles as $itemType){
                        if( $itemType["file"] == $keyFile ){
                            $cvFileByPayProcess->type =  $itemType["type"];
                        }
                    }

                    $cvFileByPayProcess->save();
                }
            }

            $countCicleWhile++;
        }

        DB::commit();
        return [
            "message" => "Registro exitoso",
            "code" => 200
        ];
    }

    public function consultPayProceesContractual($pool_id) {

        $existCvPayProcessContractor = CvPayProcessContractor::where("pool_id", $pool_id)->where("approved", 0)->first();

        if (empty($existCvPayProcessContractor)) {
            return[
                "message" => "El pago del proceso contractual no existe en el sistema",
                "code" => 500
            ];
        }

        $existCvPayProcessContractor->files;
        $existCvPayProcessContractor->comment;

        if ($this->userLoggedInId() == $existCvPayProcessContractor->to_user) {
            return $existCvPayProcessContractor;
        } else {
            return[
                "message" => "El usuario actual no esta relacionado al pago",
                "code" => 500
            ];
        }
    }

    public function selectPayByUserWithRoleFinancial($pool_id) {

        $existCvPayProcessContractor = CvPayProcessContractor::where("pool_id", $pool_id)->first();

        if (empty($existCvPayProcessContractor)) {
            return[
                "message" => "El pago del proceso contractual no existe en el sistema",
                "code" => 500
            ];
        }

        if ($this->userLoggedInRol() != 11) {
            return[
                "message" => "El usuario no cuenta con rol de financiero",
                "code" => 500
            ];
        }

        $existCvPayProcessContractor->to_user = $this->userLoggedInId();
        if ($existCvPayProcessContractor->save()) {

            return[
                "message" => "El pago del proceso contractual fue seleccionado",
                "code" => 200
            ];
        }
    }

    public function flowCancelOrApproved(Request $request) {

        $existCvPayProcessContractor = CvPayProcessContractor::find($request->pay_process_contractual_id);

        if (empty($existCvPayProcessContractor)) {
            return[
                "message" => "El pago del proceso contractual no existe en el sistema",
                "code" => 500
            ];
        }

        if ($this->userLoggedInId() != $existCvPayProcessContractor->to_user) {
            return[
                "message" => "El usuario no tiene asignado el proceso de este pago",
                "code" => 500
            ];
        }

        if ((boolean) $request->approved == true) {
            $existCvPayProcessContractor->approved = true;
            $existCvPayProcessContractor->sub_type = 4;
        } else {

            $existCvPayProcessContractor->to_user = $existCvPayProcessContractor->from_user;
            $existCvPayProcessContractor->from_user = $this->userLoggedInId();
            $existCvPayProcessContractor->sub_type = 3;

            $generalCommentController = new GeneralCommentController();

            if (empty($request->comment)) {
                return[
                    "message" => "Se requiere un comentario",
                    "code" => 500
                ];
            }

            $idComment = $generalCommentController->registerComment($request->comment);

            $commentByPayProcessContractor = new CvCommentByPayProcessContractor();
            $commentByPayProcessContractor->comment_id = $idComment;
            $commentByPayProcessContractor->pay_id = $existCvPayProcessContractor->id;
            $commentByPayProcessContractor->save();
        }
        if ($existCvPayProcessContractor->save()) {
            return[
                "message" => "Proceso exitoso",
                "code" => 200
            ];
        }
    }

    public function deleteFilesPayProcessContractual(Request $request, $file_id) {

        $deleteRegisterPayByFile = CvFileByPayProcessContractor::find($file_id);

        $generalFileController = new GeneralFileController();

        if (empty($deleteRegisterPayByFile)) {
            return[
                "message" => "El archivo seleccionado no existe en el sistema",
                "code" => 200
            ];
        }

        if ($this->userLoggedInRol() != 9) {
            return[
                "message" => "El usuario no cuenta con el rol de la coordinación apoyo de restauracion, para realizar esta acción",
                "code" => 500
            ];
        }

        $existCvPayProcessContractor = CvPayProcessContractor::where("approved", 0)->find($deleteRegisterPayByFile->pay_id);

        if ($this->userLoggedInId() != $existCvPayProcessContractor->to_user) {
            return[
                "message" => "El usuario actual no esta relacionado al pago",
                "code" => 500
            ];
        }

        if ($deleteRegisterPayByFile->delete()) {

            $file = CvFile::find($file_id);

            $typeFile = $generalFileController->extensionFile($file->name);

            $arrayFileInfo = array(
                "id_file" => $file_id,
                "state_delete" => false,
                "type_file" => $typeFile
            );
            $request->request->add($arrayFileInfo);
            return $generalFileController->deleteFiles($request);
        }
    }

}
