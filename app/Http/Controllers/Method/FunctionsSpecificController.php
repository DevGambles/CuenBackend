<?php

namespace App\Http\Controllers\Method;

use App\CvLetterIntention;
use App\CvPotentialProperty;
use App\CvProcess;
use App\CvProcessTypePsa;
use App\CvProperty;
use App\CvPropertyManagement;
use App\CvTask;
use App\CvTaskByFile;
use App\CvTaskProcess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sketch\SketchInitController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PropertyController;

class FunctionsSpecificController extends Controller {
    /*
     *  Obtener la informacion de predio potencial y replicarla al anterior flujo
     */

    public function infoPotentialProperty($potential_id) {

        $potentialProperty = CvPotentialProperty::find($potential_id);

        if (empty($potentialProperty)) {
            return [
                "Message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Validar que el flujo de predio potencial se haya terminado y este toda la informacion ---//

        $potentialPropertyCtCC = $potentialProperty->potentailPropertyByFile;
        $potentialPropertyPoll = $potentialProperty->potentialPropertyPoll;
        $potentialPropertyLetter = $potentialProperty->potentialLetterIntention;

        $potentialProperty->potentialPropertyByUserCreate;

        if (empty($potentialPropertyCtCC) || empty($potentialPropertyPoll) || empty($potentialPropertyLetter)) {
            return [
                "Message" => "El predio potencial aún no cuenta con algunos de los cuatro documento",
                "code" => 500
            ];
        } else {
            return $potentialProperty->id;
        }
    }

    /*
     * Registrar la siguiente informacion para el procedimiento registrado:
     * 
     * 1. Encuesta
     * 2. Carta de intencion
     * 3. Certificado de tradicion
     * 4. Cedula de ciudadania
     */

    public function replicateInfoOfDocumentsProcess($process_id, $potencial_id) {

        $date = new Carbon();

        $propertyPotential = CvPotentialProperty::find($potencial_id);

        //--- Registrar encuesta ---//
        $property = new CvProperty();

        $property->info_json_general = $propertyPotential->potentialPropertyPoll->info_json_general;
        $property->property_name = $propertyPotential->property_name;
        $property->main_coordinate = $propertyPotential->main_coordinate;

        //--- Relacion del predio de la encuesta a partir de un predio potencial ---//

        $propertyInfoJson = json_decode($propertyPotential->potentialPropertyPoll->info_json_general, true);

        if ($propertyPotential->property_psa == 1) {
            $property->property_correlation_id=6;
        }else{
            if (array_key_exists("property_correlation_id",$propertyInfoJson)){
                $property->property_correlation_id = $propertyInfoJson["property_correlation_id"];
                if ( $property->property_correlation_id == null){
                    $property->property_correlation_id=6;
                }
            }else{
                $property->property_correlation_id=6;
            }
        }

        $property->save();

        //--- Registrar tarea de encuesta ---//
        $taskPoll = new CvTask();

        $taskPoll->description = "Encuesta del predio potencial " . $propertyPotential->name;
        $taskPoll->date_start = $date->now();
        $taskPoll->date_end = $date->now();
        $taskPoll->task_type_id = 3;
        $taskPoll->task_status_id = 1;
        $taskPoll->task_sub_type_id = 3;
        $taskPoll->property_id = $property->id;

        $taskPoll->save();
        if ($propertyPotential->property_psa != 1) {
            //--- Realizar el registro en el buscador de algolia ---//
            $propertyController = new PropertyController();
            $propertyController->infoSearchProperty($property->id, $taskPoll->id);
        }
        //Identifica el procedimiento como predio PSA
        if ($propertyPotential->property_psa == 1) {
            $psa= new CvProcessTypePsa();
            $psa->proccess_id=$process_id;
            $psa->property_psa=TRUE;
            $psa->save();
        }
        //--- Tarea por procedimiento ---//
        $taskByProcess = new CvTaskProcess();

        $taskByProcess->task_id = $taskPoll->id;
        $taskByProcess->process_id = $process_id;

        $taskByProcess->save();

        //--- Registrar carta de intencion ---//
        $letterIntention = new CvLetterIntention();

        $letterIntention->form_letter = $propertyPotential->potentialLetterIntention->form_letter;
        $letterIntention->process_id = $process_id;
        $letterIntention->user_id = $propertyPotential->potentialPropertyByUserCreate->user_id;
        $letterIntention->type_id = 5;

        $letterIntention->save();

        //--- Registrar los documentos de la tarea ---//
        foreach ($propertyPotential->potentailPropertyByFile as $documentsPotentialProperty) {
            foreach ($propertyPotential->potentailPropertyByFilePivot as $documentsPotentialPropertyPivote) {

                if ($documentsPotentialProperty->id == $documentsPotentialPropertyPivote->file_id) {

                    //--- Registrar pivote de tareas y archivos ---//
                    $taskByFile = new CvTaskByFile();

                    $taskByFile->task_id = $taskPoll->id;
                    $subType = ($documentsPotentialPropertyPivote->type_file == "ct") ? 8 : 2;
                    $taskByFile->task_sub_type_id = $subType;
                    $taskByFile->file_id = $documentsPotentialPropertyPivote->file_id;

                    $taskByFile->save();
                }
            }
        }


        //--- Registrar croquis ---//

        $propertyInfo = json_decode($property->info_json_general, true);

        if ($propertyPotential->property_psa != 1){

            $latitude = $propertyInfo["economic_activity_in_the_property"]["latitude"];
            $longitude = $propertyInfo["economic_activity_in_the_property"]["longitude"];


            $sketchController = new SketchInitController();
            $sketchController->shearPointProperty($latitude, $longitude, $property->id, null);
        }

    }

    /*
     * Actualizar la informacion de los archivos de un procedimiento relacionados a un predio potencial
     */

    public function updateInfoFilesPotentialPropertyRelationsProcess($potential_id) {

        $potential = CvPotentialProperty::find($potential_id);

        if (empty($potential)) {
            return [
                "message" => "El predio potencial no existe en el sistema",
                "code" => 500
            ];
        }

        $filesNewTaskOfPotentialProperty = array();

        //--- Validar si el predio potencial existe en uno o mas procedimiento ---//
        $processPotential = CvProcess::where("potential_property_id", $potential->id)->get();

        try {

            if (count($processPotential) > 0) {

                //--- Recorrer los procedimientos ---//
                foreach ($processPotential as $valueProcessPotential) {

                    if (!empty($valueProcessPotential->processByTasks)) {

                        //--- Validar que la tarea de medicion no se encuentre en el ultimo subtipo del flujo ---//
                        $validateSubTypeTaskMap = TRUE;

                        foreach ($valueProcessPotential->processByTasks as $valueProcessTaskMap) {
                            if ($valueProcessTaskMap->task_type_id == 1 && $valueProcessTaskMap->task_sub_type_id == 33) {
                                $validateSubTypeTaskMap = FALSE;
                            }
                        }

                        if ($validateSubTypeTaskMap == TRUE) {

                            //--- Recorrer las tareas de cada procedimiento ---//
                            foreach ($valueProcessPotential->processByTasks as $valueProcessTask) {


                                //--- Validar que la tarea sea de tipo encuesta ---//
                                if ($valueProcessTask->task_type_id == 3) {

                                    /*
                                     * Consultar los archivos de carta de intencion y encuesta relacionados a la tarea con los del predio potencial
                                     */

                                    foreach ($potential->potentailPropertyByFilePivot as $potentialByFile) {
                                        foreach ($valueProcessTask->taskFilePivot as $taskByFile) {

                                            if (!empty($taskByFile)) {
                                                if (isset($taskByFile->file_id)) {
                                                    if ($potentialByFile->file_id != $taskByFile->file_id) {

                                                        //--- Eliminar los archivos que ya no existe en el predio potencial ---//
                                                        $taskByFile->delete();
                                                        array_push($filesNewTaskOfPotentialProperty, $potentialByFile);
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    /*
                                     * Validar si existen los mismos archivos, sino:
                                     * Insertar los nuevos archivos del predio potencial
                                     */

                                    foreach (array_values(array_unique($filesNewTaskOfPotentialProperty)) as $valueNewFilesPotentialProperty) {

                                        $taskByFileExists = CvTaskByFile::where("task_id", $valueProcessTask->id)->where("file_id", $valueNewFilesPotentialProperty->file_id)->exists();

                                        if ($taskByFileExists == false) {

                                            $taskByFileNew = new CvTaskByFile();

                                            $taskByFileNew->task_id = $valueProcessTask->id;
                                            $subType = ($valueNewFilesPotentialProperty->type_file == "ct") ? 8 : 2;
                                            $taskByFileNew->task_sub_type_id = $subType;
                                            $taskByFileNew->file_id = $valueNewFilesPotentialProperty->file_id;

                                            $taskByFileNew->save();
                                        }
                                    }

                                    /*
                                     * Actualizar la informacion de encuesta y carta de intencion
                                     */

                                    if (!empty($valueProcessTask->property->info_json_general)) {

                                        $updatePoll = CvProperty::find($valueProcessTask->property->id);
                                        $updatePoll->info_json_general = $potential->potentialPropertyPoll->info_json_general;
                                        $updatePoll->save();
                                    }

                                    if (!empty($valueProcessPotential->letterIntention)) {

                                        $updatePoll = CvLetterIntention::find($valueProcessPotential->letterIntention->id);
                                        $updatePoll->form_letter = $potential->potentialLetterIntention->form_letter;
                                        $updatePoll->save();
                                    }
                                }
                            }
                        }
                    }
                }

                return 200;
            }
        } catch (Exception $ex) {
            $ex->getMessage();
        }
    }

    public function propertyManagement(Request $json) {
        $management = new CvPropertyManagement;
        $management->predial_json = json_encode($json->all(), true);
        $management->save();

        $add = CvPropertyManagement::all()->last();
        $fp = fopen('temporal\\data.json', 'w');
        fwrite($fp, $add->predial_json);
        fclose($fp);

        return [
            "message" => "Gestion almacenada exitosamente",
            "code" => 200
        ];
    }

    public function getPropertyManagement()
    {
        return "192.168.0.34:8000\\temporal\\data.json";
    }

    //Arma el objeto array para traer contribucion de actividades de comando y control para tareas abiertas
    public function objectForTaskSpecial($comand)
    {
        $total_balance=0;
        $total_contribution=0;
        $total_contribution_specie=0;
        $total_paid=0;
        $info=array();
        $type1=array();
        $type2=array();

        foreach ($comand as $psa){
            if ($psa->type == 1){
                array_push($type1,array(
                    'id'=>$psa->id,
                    'inversion'=>$psa->inversion,
                    'paid'=>$psa->paid,
                    'committed'=>$psa->committed,
                    'balance'=>$psa->balance,
                    'associated_id'=>$psa->associated_id,
                    'associated_name'=>$psa->thisisassociate->name,
                ));
            }else{

                array_push($type2,array(
                    'id'=>$psa->id,
                    'inversion_specie'=>$psa->inversion_species,
                    'paid'=>$psa->paid,
                    'committed'=>$psa->committed,
                    'balance'=>$psa->balance,
                    'associated_id'=>$psa->associated_id,
                    'associated_name'=>$psa->thisisassociate->name,
                    'species'=>$psa->species,
                ));
            }
            $total_balance=  $total_balance+ $psa->balance;
            $total_contribution=  $total_contribution+ $psa->inversion;
            $total_paid=  $total_paid+ $psa->paid;
            $total_contribution_specie=$total_contribution_specie+$psa->inversion_species;

        }

        $info['type_1']=$type1;
        $info['type_2']=$type2;
        $info['total_balance']=$total_balance;
        $info['total_contribution']=$total_contribution;
        $info['total_contribution_specie']=$total_contribution_specie;
        $info['total_paid']=$total_paid;

        return $info;
    }


}
