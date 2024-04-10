<?php

namespace App\Http\Controllers\General;

use App\CvImgBase64TaskByGeneral;
use App\CvTask;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralFileController;
use DB;
use Illuminate\Http\Request;

class GeneralTaskFileBase64Controller extends Controller
{
    /**========================================================
     * Registrar archivos en base 64 de tareas de medicion
     *========================================================*/

    public function registerFileTaskGeneralBase64(Request $request)
    {

        //--- Transaccion ---//
        DB::beginTransaction();

        $task = CvTask::find($request->task_id);

        if (empty($task)) {
            return response([
                "message" => "La tarea no existe en el sistema",
                "code" => 409,
            ], 409);
        }

        if ($task->task_type_id != 1) {
            return response([
                "message" => "La tarea no es de tipo de medición de acciones",
                "code" => 409,
            ], 409);
        }

        //--- Obtener información de los archivos ---//
        $images = $request->imgs;

        if (empty($images)) {
            return response([
                "message" => "No se encuentran imagenes en la peticion",
                "code" => 409,
            ], 409);
        }

        foreach ($images as $itemImages) {
            if (isset($itemImages)) {

                //--- Funcion general para guardar archivo en base 64 ---//
                $instanceGeneralFile = new GeneralFileController();
                $idFileBase64 = $instanceGeneralFile->saveImgBase64($itemImages);

                //--- Vincular archivos con la tarea ---//
                if ($idFileBase64 != false) {

                    $fileImg = new CvImgBase64TaskByGeneral();
                    $fileImg->file_id = $idFileBase64;
                    $fileImg->task_id = $request->task_id;
                    $fileImg->user_id = $this->userLoggedInId();

                    if ($fileImg->save()) {
                        DB::commit();
                    } else {
                        DB::rollback();
                    }

                } else {
                    DB::rollback();
                }
            }
        }

        return response([
            "message" => "Registro exitoso",
            "code" => 200,
        ], 200);

    }

    /**===========================================================
    * Consultar archivos de base 64 por tareas generales "Medicion
    *=============================================================*/

    public function consultGeneralsTaskFile64($task_id) {

        $task = CvTask::find($task_id);

        if (empty($task)) {
            return response([
                "message" => "La tarea no existe en el sistema",
                "code" => 409,
            ], 409);
        }

        if ($task->task_type_id != 1) {
            return response([
                "message" => "La tarea no es de tipo de medición de acciones",
                "code" => 409,
            ], 409);
        }

        if (!empty($task->taskFileBase64)) {

            foreach ($task->taskFileBase64 as $itemTaskFileBase64) {
                
                unset($itemTaskFileBase64->state);
                unset($itemTaskFileBase64->created_at);
                unset($itemTaskFileBase64->updated_at);
                unset($itemTaskFileBase64->pivot);

            }

        }

        return $task->taskFileBase64;

    }

}
