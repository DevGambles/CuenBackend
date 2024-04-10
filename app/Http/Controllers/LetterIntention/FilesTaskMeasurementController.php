<?php

namespace App\Http\Controllers\LetterIntention;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralFileController;
use App\CvTask;
use App\CvLetterIntention;
use App\CvFileByLetterIntention;

class FilesTaskMeasurementController extends Controller {

    //*** Registrar talonario para tarea de medicion relacionado a la carta de intención ***//
    public function registerFileTask(Request $data) {

        $task = CvTask::find($data->task_id);

        if ($task == null) {
            return [
                "message" => "La tarea no existe en el sistema",
                "code" => 500
            ];
        }

        //*** Obtener la carta de intecion del procedimiento y la tarea de medicion con el sub tipo 4 ***//
        if ($task->task_sub_type_id != $data->type) {
            return [
                "message" => "La tarea no se encuentra con el sub tipo 4 de medir predio",
                "code" => 500
            ];
        }

        $processByTask = $task->process;

        $idProcess = 0;

        if (is_object($processByTask)) {
            foreach ($processByTask as $value) {
                $idProcess = $value->pivot->process_id;
            }
        }

        //*** Consultar carta de intecion ***//
        if ($idProcess == 0) {
            return [
                "message" => "El procedimiento de la tarea seleccionada, no existe en el sistema",
                "code" => 500
            ];
        }

        $letterIntention = CvLetterIntention::where("process_id", $idProcess)->first();

        //*** Registrar archivos ***//
        $arrayFiles = $data->file('files');

        if (is_array($arrayFiles) && !empty($arrayFiles)) {

            foreach ($arrayFiles as $file) {

                //--- Guardar archivos de forma local ---//
                $generalFileController = new GeneralFileController();
                $nameFile = $generalFileController->saveFilesShapeGeneral($file);

                //--- Guardar archivos en base de datos ---//
                $idFileSave = $generalFileController->saveFilesTableBD($nameFile);

                $fileByLetterIntention = new CvFileByLetterIntention();

                $fileByLetterIntention->file_id = $idFileSave;
                $fileByLetterIntention->letter_intention_id = $letterIntention->id;

                $fileByLetterIntention->save();
            }
        } else {
            return[
                "message" => "La variables Files no es de tipo array",
                "code" => 500
            ];
        }
    }

    //*** Consultar talonario para tarea de medicion relacionado a la carta de intención ***//
    public function consultFileTask($task_id) {

        $task = CvTask::find($task_id);

        if ($task == null) {
            return [
                "message" => "La tarea no existe en el sistema",
                "code" => 500
            ];
        }

        //*** Obtener la carta de intecion del procedimiento y la tarea de medicion con el sub tipo 4 ***//
        if ($task->task_sub_type_id != 4) {
            return [
                "message" => "La tarea no se encuentra con el sub tipo 4 de medir predio",
                "code" => 500
            ];
        }

        $processByTask = $task->process;

        $idProcess = 0;

        if (is_object($processByTask)) {
            foreach ($processByTask as $value) {
                $idProcess = $value->pivot->process_id;
            }
        }

        //*** Consultar carta de intencion y sus documentos ***//
        $letterIntention = CvLetterIntention::where("process_id", $idProcess)->first();

        $files = $letterIntention->files;
        $arrayFile = array();

        foreach ($files as $itemFile) {

            if ($itemFile->state_delete == 0) {
                array_push($arrayFile, $itemFile);
            }
        }
        
        return $arrayFile;
    }

}
