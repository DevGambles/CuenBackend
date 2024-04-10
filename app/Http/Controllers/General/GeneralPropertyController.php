<?php

namespace App\Http\Controllers\General;

use App\CvPotentialLetterIntention;
use App\CvPotentialPropertyPoll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvProperty;
use App\CvProcess;
use App\CvPotentialProperty;
use App\CvPropertyByUser;
use App\CvTask;
use App\Http\Controllers\General\GeneralQuotesSaveController;

class GeneralPropertyController extends Controller {

    //*** Registrar predio potencial ***//
    public function registerPropertyPotential(Request $request) {

        $property = new CvPotentialProperty();
        $property->property_name = $request->name;
        $property->main_coordinate = $request->lat . "," . $request->lng;
        $property->potential_sub_type_id = 1;

        if ($this->userLoggedInRol() == 3 || $this->userLoggedInRol() == 4 || $this->userLoggedInRol() == 9 || $this->userLoggedInRol() == 10 || $this->userLoggedInRol() == 16) {
            if ($request->psa == 1){
                $property->property_psa = TRUE;
                $property->potential_sub_type_id = 4;
            }
        }

        if ($property->save()) {

            //--- Guardar la relacion del predio potencial con el usuario autenticado ---//
            $potentialPropertyByUser = new CvPropertyByUser();

            $potentialPropertyByUser->property_id = $property->id;
            $potentialPropertyByUser->user_id = $this->userLoggedInId();

            $potentialPropertyByUser->save();

            //--- Validar rol de guarda cuenca ---//
            if ($this->userLoggedInRol() == 3 || $this->userLoggedInRol() == 4 || $this->userLoggedInRol() == 9 || $this->userLoggedInRol() == 10 || $this->userLoggedInRol() == 16) {
                $generalQuotesGuard = new GeneralQuotesSaveController();
                $generalQuotesGuard->consultQuotaTotalApproved($property->id, $this->userLoggedInId());
            }
            if ($this->userLoggedInRol() == 3 || $this->userLoggedInRol() == 4 || $this->userLoggedInRol() == 9 || $this->userLoggedInRol() == 10 || $this->userLoggedInRol() == 16) {
            if ($request->psa == 1){
                $psa=('{"info_general": {},"form_letter": {},"potential_id": '.$property->id.',"psa": true}');
            }else{
                $psa=('{"info_general": {},"form_letter": {},"potential_id": '.$property->id.',"psa": false}');
            }
            }else{
                $psa=('{"info_general": {},"form_letter": {},"potential_id": '.$property->id.',"psa": false}');
            }
            if ($this->userLoggedInRol() == 3 || $this->userLoggedInRol() == 4 || $this->userLoggedInRol() == 9 || $this->userLoggedInRol() == 10 || $this->userLoggedInRol() == 16) {
                $poll= new CvPotentialPropertyPoll();
                $poll->info_json_general=$psa;
                $poll->potential_property_id= $property->id;
                $poll->save();

                $letter= new CvPotentialLetterIntention();
                $letter->form_letter=$psa;
                $letter->user_id= $this->userLoggedInId();
                $letter->potential_property_id= $property->id;
                $letter->save();


            }else{
                return [
                    "message" => "El rol no tiene permisos para crear predio PSA",
                    "code" => 500
                ];
            }

            return [
                "message" => "Registro exitoso",
                "code" => 200,
                "id" => $property->id
            ];
        }


    }

    //*** Consultar si existe un predio potencial en el procedimiento ***//
    public function consultProcessPropertyPotentialExist($id) {

        //--- Consultar procedimiento ---//
        $process = CvProcess::find($id);

        // --- Información del las propiedades potenciales --- //
        $info = array();

        if ($process != "") {

            // --- Consultar todas la tareas con predios reales y potenciales --- //
            $allProperties = CvProperty::get();

            //--- Recorrer las tareas del procedimiento para determinar cual es el predio potencial vinculado ---//
            $property = 0;

            foreach ($process->processByTasks as $processByTask) {

                $property = $processByTask->property_id;
            }

            if ($property != 0) {

                $allProperties = CvProperty::find($property);
                array_push($info, array("select" => true), $allProperties);

                return $info;
            }

            //--- Si no existe predio real o potencial ---//

            array_push($info, array("select" => false));

            foreach ($allProperties as $properties) {

                // --- Consultar predios potenciales --- //

                $propertiesPotentials = CvProperty::where("id", $properties->id)->first();
                $propertiesPotentials->task;
                if (count($propertiesPotentials->task) == 0 && $propertiesPotentials->main_coordinate != "") {
                    array_push($info, $propertiesPotentials);
                }
            }

            return $info;
        }

        return "El procedimiento no existe";
    }

    //*** Consultar el croquis de la encuesta de un procedimiento ***//
    public function consultSketchProcessProperty($potential_id) {

        $potential = CvProcess::where("potential_property_id", $potential_id)->first();
        if (empty($potential)) {
            return[];
        }

        //--- Enviar croquis de un procedimiento ---//
        if (!empty($potential->processByTasks)) {

            foreach ($potential->processByTasks as $value) {
                if ($value->task_type_id == 3) {
                    $property = $value->property;
                    if (!empty($property)) {
                        $sketch = $property->properySketch;
                        if (!empty($sketch)) {
                            return json_decode($sketch->info_json_general, true);
                        } else {
                            return [
                                "message" => "El croquis no existe en el sistema",
                                "oode" => 500
                            ];
                        }
                    } else {
                        return [
                            "message" => "La encuesta no existe en el sistema",
                            "oode" => 500
                        ];
                    }
                }
            }
        }
    }

}
