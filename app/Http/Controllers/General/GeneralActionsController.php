<?php

namespace App\Http\Controllers\General;

use App\CvActions;
use App\CvActionsProcess;
use App\CvBudget;
use App\CvBudgetActionMaterial;
use App\CvUnits;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralActionsController extends Controller {

    //--- Consultar las acciones de presupuesto ---//

    public function actions($type) {

        $result = array();
        $acciones = CvActions::where('type', $type)->get();
        foreach ($acciones as $accion) {
            if ($accion->id != 4 && $accion->id != 8 && $accion->id != 27 && $accion->id != 41){
                array_push($result, array(
                        "id" => $accion->id,
                        "name" => $accion->name,
                        "color" => $accion->color,
                        "color_fill" => $accion->color_fill,
                        "type" => $accion->type
                    )
                );
            }
        }
        return $result;
    }

    //--- Consultar los materiales del presupuesto ---//

    public function materials($action) {

        $result = array();
        $materials = CvBudgetActionMaterial::where('action_id', $action)->get();
        foreach ($materials as $material) {

            array_push($result, array(
                    "id" => $material->budgetPriceMaterial->id,
                    "name" => $material->budgetPriceMaterial->name,
                    "price" => $material->budgetPriceMaterial->price,
                    "measurement" => $material->budgetPriceMaterial->measurement,
                    "type" => $material->budgetPriceMaterial->type,
                    "unit" => CvUnits::find($material->budgetPriceMaterial->unit_id)->name,
                )
            );
        }
        return $result;
    }

    public function allMaterials() {
        $result = array();
        $actions = CvActions::all();
        $materials = CvBudgetActionMaterial::all();
        foreach ($actions as $action) {
            $materialResult = array(
                "action" => $action->id,
                "materials" => array()
            );
            foreach ($materials as $material) {
                if ($action->id == $material->action_id) {
                    array_push($materialResult["materials"], array(
                        "id" => $material->budgetPriceMaterial->id,
                        "name" => $material->budgetPriceMaterial->name,
                        "price" => $material->budgetPriceMaterial->price,
                        "measurement" => $material->budgetPriceMaterial->measurement,
                        "type" => $material->budgetPriceMaterial->type,
                        "unit" => CvUnits::find($material->budgetPriceMaterial->unit_id)->name
                    ));
                }
            }
            array_push($result, $materialResult);
        }
        return $result;
    }

    public function allActionForMaterial() {
        $result=array();
        $materials = CvBudgetActionMaterial::all();
        foreach ($materials as $detail_material) {
            $detail_material->budgetPriceMaterial;
            array_push($result, array(
                    "action_id" => $detail_material->actionOne->id,
                    "action_name" => $detail_material->actionOne->name,
                    "action_type" => $detail_material->actionOne->type,
                    "material_id" => $detail_material->budgetPriceMaterial->id,
                    "material_name" => $detail_material->budgetPriceMaterial->name,
                    "color" => $detail_material->actionOne->color,
                    "fill_color" => $detail_material->actionOne->color_fill,
                )
            );
        }
        return $result;
    }

    public function getActionsGoodPractices(){
        $allActionsGoodPratices = CvActions::where('good_practicess', 1)->get();
        return $allActionsGoodPratices;
    }
    public function saveActionsGoodPractices(Request $request){

        $modelBudgest = new CvBudget();
        $modelBudgetActionMaterial = CvBudgetActionMaterial::where('action_id', $request->action_id)->get();

        $priceMaterial = $modelBudgetActionMaterial[0]->budgetPriceMaterial;
        $length= $request->length;
        if( $request->length == 0){
            $length =1;
        }
        $modelBudgest->value = $length * $priceMaterial->price;
        $modelBudgest->length = $length;
        $modelBudgest->hash_map = null;
        $modelBudgest->task_id = $request->task_id;
        $modelBudgest->action_material_id = $modelBudgetActionMaterial[0]->id;
        $modelBudgest->good_practicess = 1;

        if ($modelBudgest->save()){
            return [
                'message' => 'registro exitoso',
                'code' => 200,
            ];
        }
        else{
            return [
                'message' => 'error',
                'code' => 500,
            ];
        }
    }

}
