<?php

namespace App\Http\Controllers\Report;

use App\CvPoolProcess;
use App\CvPotentialProperty;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportIPHController extends Controller
{
    public function reportIPHYear($year)
    {
        $budgetactionsTotalFE=0;
        $budgetactionsPorcentFE=0;
        $budgetactionsTotalRG=0;
        $budgetactionsPorcentRG=0;
        $jsonPoolPotentialPropertyLafe=array();
        $jsonPoolPotentialPropertyRiogrande=array();
        $jsonPoolPotentialProperty=array();

        $areatypesForest=array();
        $all_properties=CvPotentialProperty::all();
        $jsonPoolPotentialProperty = $this->AllAreaTotalProperties($year, $all_properties, $jsonPoolPotentialProperty);
        //Areas la FE
        for($i=1; $i <= 12; $i++){
            $areatypesRestaurationActive=0;
            $areatypesGoodPractices=0;
            foreach ($all_properties as $detail_property) {
                if ($detail_property->created_at->format('m') == $i){
                    if ($detail_property->created_at->format('Y') == $year) {//Todos los predios creados en el año entrante
                        if (!empty($detail_property->potentialPropertyPoll)) {//Encuesta de cada predio
                            $date_json_property = json_decode($detail_property->potentialPropertyPoll->info_json_general, true);
                            if (array_key_exists('property_reservoir', $date_json_property)) {//la cuenca
                                if ($date_json_property['property_reservoir'] != null) {
                                    // si la cuenca es la FE o Rios Grande (RG)
                                    if (strtolower($date_json_property['property_reservoir']) == "la fe") {
                                        if ($date_json_property['economic_activity_in_the_property']['property_area'] != null) {
                                            $proces_all= $detail_property->processMany;
                                            //un predio puede estar en muchos procedimientos
                                            foreach ($proces_all as $dateil_proces){
                                                $task_measurent= $dateil_proces->processByTasks->where('task_sub_type_id','>=', 4)->first();
                                                if (!empty($task_measurent)){
                                                    //El procedimiento tiene una tarea de medicion que tiene muchas acciones
                                                    $all_budgets= $task_measurent->budget;
                                                    if (count($all_budgets) > 0){
                                                        //el procedmineto tiene muchas acciones
                                                        foreach ($all_budgets as $budget_detail){
                                                            if (!empty($budget_detail->budgetContractor)){
                                                                //las acciones pueden estar o no en ejecucion
                                                                if (!empty($budget_detail->budgetContractor->excecution)){
                                                                    $budgetactionsPorcentFE=$budgetactionsPorcentFE+1;
                                                                }
                                                            }
                                                        }
                                                        $budgetactionsTotalFE=$budgetactionsTotalFE+count($all_budgets);
                                                        $porcenteExecutionFE=0;
                                                        if ($budgetactionsTotalFE != 0){
                                                            $porcenteExecutionFE=($budgetactionsPorcentFE * 100)/$budgetactionsTotalFE;
                                                        }
                                                        //solo el 50% de las acciones en nejecucion son medidas
                                                        if ($porcenteExecutionFE >= 50){
                                                            //Acciones en su 50% ejecutadas se dividen en buenas practicas restauracion y bosques
                                                            foreach ($all_budgets as $detail_budget){
                                                                $action_detail=$detail_budget->actionsMaterials->action;
                                                                if($action_detail->good_practicess == 1){
                                                                    $areatypesGoodPractices=$areatypesGoodPractices+$detail_budget->length;
                                                                }else{
                                                                    $areatypesRestaurationActive+$areatypesRestaurationActive+$detail_budget->length;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            array_push($jsonPoolPotentialPropertyLafe,array(
                "month"=>$i,
                "area_practices"=>$areatypesGoodPractices,
                "area_restauration"=>$areatypesRestaurationActive
            ));
        }
        //Areas Rio Grande
        for($i=1; $i <= 12; $i++){
            $areatypesRestaurationActive=0;
            $areatypesGoodPractices=0;
            foreach ($all_properties as $detail_property) {
                if ($detail_property->created_at->format('m') == $i){
                    if ($detail_property->created_at->format('Y') == $year) {//Todos los predios creados en el año entrante
                        if (!empty($detail_property->potentialPropertyPoll)) {//Encuesta de cada predio
                            $date_json_property = json_decode($detail_property->potentialPropertyPoll->info_json_general, true);
                            if (array_key_exists('property_reservoir', $date_json_property)) {//la cuenca
                                if ($date_json_property['property_reservoir'] != null) {
                                    // si la cuenca es la FE o Rios Grande (RG)
                                    if (strtolower($date_json_property['property_reservoir']) == "rio grande") {
                                        if ($date_json_property['economic_activity_in_the_property']['property_area'] != null) {
                                            $proces_all= $detail_property->processMany;
                                            foreach ($proces_all as $dateil_proces){
                                                $task_measurent= $dateil_proces->processByTasks->where('task_sub_type_id','>=', 4)->first();
                                                if (!empty($task_measurent)){
                                                    $all_budgets= $task_measurent->budget;
                                                    if (count($all_budgets) > 0){
                                                        foreach ($all_budgets as $budget_detail){
                                                            if (!empty($budget_detail->budgetContractor)){
                                                                if (!empty($budget_detail->budgetContractor->excecution)){
                                                                    $budgetactionsPorcentRG=$budgetactionsPorcentRG+1;
                                                                }
                                                            }
                                                        }
                                                        $budgetactionsTotalRG= $budgetactionsTotalRG+count($all_budgets);
                                                        $porcenteExecutionRG=0;
                                                        if ($budgetactionsTotalRG != 0){
                                                            $porcenteExecutionRG=($budgetactionsPorcentRG * 100)/$budgetactionsTotalRG;
                                                        }
                                                        if ($porcenteExecutionRG >= 50){
                                                            foreach ($all_budgets as $detail_budget){
                                                                $action_detail=$detail_budget->actionsMaterials->action;
                                                                if($action_detail->good_practicess == 1){
                                                                    $areatypesGoodPractices=$areatypesGoodPractices+$detail_budget->length;
                                                                }else{
                                                                    $areatypesRestaurationActive+$areatypesRestaurationActive+$detail_budget->length;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            array_push($jsonPoolPotentialPropertyRiogrande,array(
                "month"=>$i,
                "area_practices"=>$areatypesGoodPractices,
                "area_restauration"=>$areatypesRestaurationActive
            ));
        }
        $jsonPoolPotentialProperty['LaFe']['months']=$jsonPoolPotentialPropertyLafe;
        $jsonPoolPotentialProperty['RioGrande']['months']=$jsonPoolPotentialPropertyRiogrande;
        return $jsonPoolPotentialProperty;
    }

    /**
     * @param $year
     * @param $all_properties
     * @param $jsonPoolPotentialProperty
     * @return mixed
     */
    private function AllAreaTotalProperties($year, $all_properties, $jsonPoolPotentialProperty)
    {
        $area_totalF = 0;
        $area_totalR = 0;
        foreach ($all_properties as $detail_property) {
            if ($detail_property->created_at->format('Y') == $year) {
                if (!empty($detail_property->potentialPropertyPoll)) {
                    $date_json_property = json_decode($detail_property->potentialPropertyPoll->info_json_general, true);
                    if (array_key_exists('property_reservoir', $date_json_property)) {
                        if ($date_json_property['property_reservoir'] != null) {
                            if (strtolower($date_json_property['property_reservoir']) == "la fe") {
                                if ($date_json_property['economic_activity_in_the_property']['property_area'] != null) {
                                    $area_totalF = $area_totalF + $date_json_property['economic_activity_in_the_property']['property_area'];
                                }
                            } else if (strtolower($date_json_property['property_reservoir']) == "rio grande") {
                                if ($date_json_property['economic_activity_in_the_property']['property_area'] != null) {
                                    $area_totalR = $area_totalR + $date_json_property['economic_activity_in_the_property']['property_area'];
                                }
                            }
                        }
                    }
                }
            }
        }
        $jsonPoolPotentialProperty['LaFe']['area_total'] = $area_totalF;
        $jsonPoolPotentialProperty['RioGrande']['area_total'] = $area_totalR;
        return $jsonPoolPotentialProperty;
    }
}
