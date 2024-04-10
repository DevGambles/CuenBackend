<?php

namespace App\Http\Controllers\General;

use App\CvActionByActivity;
use App\CvActions;
use App\CvAssociated;
use App\CvAssociatedContribution;
use App\CvBudget;
use App\CvBudgetActionMaterial;
use App\CvContributionPerShare;
use App\CvDetailOriginResource;
use App\CvGeoJson;
use App\CvOriginResource;
use App\CvProcess;
use App\CvTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralBudgetController extends Controller
{

    //*** Consultar presupuesto por tarea ***//

    public function consultBudgetByTask($id)
    {
        $info = array();
        $associated = array();
        $task = CvTask::find($id);

        if (!empty($task)) {
            if (!empty($task->budget)) {
                //Valida si en la contribucion de actividades hay asociados inversionistas

                $process = CvProcess::find($task->process[0]['id']);

                foreach ($process->processByProjectByActivity as $activities) {

                    $contriAssociated = CvAssociatedContribution::where('project_activity_id', $activities->id)->first();
                    if ($contriAssociated->exists()) {
                        //si hay inversionistas en la actividad, guarda en el array los asociados que invierten en la actividad
                        foreach ($contriAssociated->get() as $value) {

                            array_push($associated, array(
                                "id" => $value->associated_id,
                                "name" => CvAssociated::find($value->associated_id)->name,
                                "contribution_associated_id" => $value->id,
                                "task_id" => $task->id,
                            ));
                        }
                    }
                }
                foreach ($task->budget as $budget) {

                    $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                    $asociateId = CvContributionPerShare::where("budget_id", $budget->id)->first();
                    $dateAssociated = "";
                    if ($asociateId) {
                        $dateAssociated = $asociateId->associated_id;
                    }
                    if ($budget->length == 0) {
                        $budget_value = $budgetaction->budgetPriceMaterial->price;
                        $budget_length = 1;
                    } else {
                        $budget_value = $budget->value;
                        $budget_length = $budget->length;
                    }
                    array_push($info, array(
                        "id" => $budget->id,
                        "value" => $budget_value,
                        "length" => $budget_length,
                        "hash_map" => $budget->hash_map,
                        "task_id" => $budget->task_id,
                        "action_material_id" => $budget->action_material_id,
                        "action_name" => $budgetaction->action->name,
                        "material_name" => $budgetaction->budgetPriceMaterial->name,
                        "created_at" => $task->created_at->format('Y-m-d H:i:s'),
                        "updated_at" => $task->updated_at->format('Y-m-d H:i:s'),
                        "associated" => $associated,
                        "associated_per_shares" => $dateAssociated,
                    ));
                }
            }
            return $info;
        } else {

            return [
                "message" => "La tarea no se encuntra en el sistema",
                "response_code" => 200,
            ];
        }
    }

    //*** Consultar todos los presupuestos ***//

    public function consultBudgetAll()
    {

        $info = array();

        //--- Consultar procedimientos ---//

        $process = CvProcess::get();

        //--- Consultar la tareas de los procedimientos ---//

        foreach ($process as $processByTask) {

            foreach ($processByTask->processByTasks as $processTasks) {

                $processTasks;

                $taskByBudget = CvTask::find($processTasks->id)->budget;
                $taskProperty = CvTask::find($processTasks->id)->property;

                //--- Sumar el presupuesto por tarea ---//
                $valueTotal = 0;

                foreach ($taskByBudget as $budgetTotal) {

                    $valueTotal = $valueTotal + $budgetTotal->value;
                }

                if (count($taskByBudget) > 0) {
                    array_push($info, array(
                        "procedimiento" => array(
                            "id" => $processByTask->id,
                            "name" => $processByTask->name,
                            "description" => $processByTask->description,
                        ),
                        "predio" => $taskProperty,
                        "tarea" => array(
                            "id" => $processTasks->id,
                            "title" => $processTasks->title,
                            "description" => $processTasks->description,
                            "date_start" => $processTasks->date_start,
                            "date_end" => $processTasks->date_end,
                        ),
                        "presupuesto" => $valueTotal,
                    ));
                }
            }
        }

        return $info;
    }

    public function consultbudgetgeojson($idtask)
    {
        $info = array();
        $task = CvTask::find($idtask);
        if ($task && $task->task_type_id == 1) {
            $budget = CvBudget::where('task_id', $task->id)->get();
            $geo = CvGeoJson::where('task_id', $task->id)->get();
            array_push($info, array(
                "budget" => $budget,
                "geojson" => $geo,
            ));
            return $info;
        } else {
            return [
                "message" => "La tarea no se encuntra en el sistema",
                "response_code" => 200,
            ];
        }
    }

    public function budgetActionRestoration($id_process)
    {

        $process = CvProcess::find($id_process);
        if (empty($process)) {
            return [
                "message" => "El procedimineto no existe en el sistema",
                "code" => 500,
            ];
        }

        $info = array();
        $activities = array();
        $manteniment = array();
        $goodPractices = array();
        $conservationArea = array();
        $tasks = $process->processByTasks;
        $valueRestoration = 0;
        $valueManteniment = 0;
        $valuePractices = 0;
        $areaReferent = 0;
        $areaNArea = 0;
        $areaRArea = 0;
        $areaLArea = 0;
        $totalAreaConservation = 0;
        $sumAreaRNL = 1;
        $aislament = "";

        /**==============================
        * Porcentajes por el presupuesto
        *===============================*/
        $administration = 0;
        $utility = 0;
        $iva = 0;

        foreach ($tasks as $tasky) {

            $json = json_decode($tasky->property->info_json_general, true);
            $info["basin_name"] = $tasky->property->property_name;

            $info["property_priority"] = '';
            $info["municipality"] = '';
            if (array_key_exists('property_correlation', $json))//georgi add
                $info["property_priority"] = $json['property_correlation'];
            if (array_key_exists('municipality', $json))//georgi add
                $info["municipality"] = $json['municipality'];

            //--Busca tarea tipo medicion--//
            if ($tasky->task_sub_type_id >= 4) {

                $taskgeojson = $tasky->geoJson;
                //--Ciclo para mapa--//
                $hash = "";

                foreach ($tasky->budget as $detail_budget) {

                    $action_clasificate = $detail_budget->actionsMaterials->action->types->where('id', '>=', 6)->where('id', '<=', 7)->first();

                    //--tipo de geometria del mapa--//
                    $totalAreaConservation = $totalAreaConservation + $detail_budget->length;
                    array_push($conservationArea, array(
                        "action" => $detail_budget->actionsMaterials->action->name,
                        "area" => $detail_budget->length,
                        "polygon" => $detail_budget->polygon,
                        "percentage" => $detail_budget->length,
                    ));
                    //--Hash para validar el mapa almacenado en el presupuesto--//
                    $hash = $detail_budget->hash_map;
                }

                foreach ($tasky->budget as $budget) {

                    /**=================================================================================================================
                     * Obtener los porcentajes del primer presupuesto ya que todos deben compatir el mismo valor por tarea y presupuesto
                     *==================================================================================================================*/
                    $administration = $budget->administration;
                    $utility = $budget->utility;
                    $iva = $budget->iva;

                    //Validar el mapa almacenado en el presupuesto--//
                    if ($hash == $budget->hash_map) {
                        $areaReferent = $budget->hash_map;
                    }
                    $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                    //--Tipo de aislamiento--//
                    $typesActivities = $budgetaction->action->types;
                    foreach ($typesActivities as $typeAislament) {
                        if ($typeAislament->id == 2) {
                            $aislament = $typeAislament->name;
                        } elseif ($typeAislament->id == 3) {
                            $aislament = $typeAislament->name;
                        }
                    }

                    $budget_value = $budget->value;
                    $budget_length = $budget->length;

                    $array_contribution = array();
                    $byactivite = $budget->actionsMaterials->actionOne->byActivite;
                    if (!empty($byactivite)) {
                        $contribution = CvAssociatedContribution::where('project_activity_id', $byactivite->activity_id)->where('year', date('Y'))->where('type', 1);
                        if ($contribution->exists()) {
                            foreach ($contribution->get() as $contri) {
                                $contribution = false;
                                $modelResource = $contri->originResource;
                                if ($modelResource) {
                                    $contribution = true;
                                }
                                $valuContri = 0;
                                $detail_origin = CvDetailOriginResource::where('budget_id', $budget->id)->where('associated_id', $contri->associated->id);
                                if ($detail_origin->exists()) {
                                    $valuContri = $detail_origin->first()->ultimate_committed;
                                }
                                array_push($array_contribution, array(
                                    "contribution_associated_id" => $contri->id,
                                    "asociated_name" => $contri->associated->name,
                                    "associated_id" => $contri->associated->id,
                                    "contribution" => $contribution,
                                    "value" => $valuContri,
                                    "budget" => $budget->id,
                                ));
                            }
                        }}
                    //--Acciones de Buenas practicas--//
                    if ($this->typeAction($typesActivities) == 3) {
                        $valuePractices = $valuePractices + $budget->value;
                        array_push($goodPractices, array(
                            "id" => $budget->id,
                            "value" => $budget_value,
                            "length" => $budget_length,
                            "value_unit" => $budgetaction->budgetPriceMaterial->price,
                            "units" => $budgetaction->budgetPriceMaterial->units->name,
                            "hash_map" => $budget->hash_map,
                            "task_id" => $budget->task_id,
                            "process_id" => $id_process,
                            "action_material_id" => $budget->action_material_id,
                            "material_name" => $budgetaction->budgetPriceMaterial->name,
                            "action_name" => $budgetaction->action->name,
                            "action_id" => $budgetaction->action->id,
                            "area_referent" => $areaReferent,
                            "aislament" => $aislament,
                            "contribution" => $array_contribution,
                        ));
                    }
                    //--Acciones de Restauracion--//
                    if ($this->typeAction($typesActivities) == 2) {
                        $idtypeaction = $budgetaction->action->types->where('id', '>=', 5)->where('id', '<=', 7)->first();

                        if (isset($idtypeaction)) {
                            $sumAreaRNL = $sumAreaRNL + $budget->length;
                            if ($idtypeaction->id == 5) {
                                $areaRArea = $areaRArea + $budget->length;
                            }
                            if ($idtypeaction->id == 6) {
                                $areaNArea = $areaNArea + $budget->length;
                            }
                            if ($idtypeaction->id == 7) {
                                $areaLArea = $areaLArea + $budget->length;
                            }
                        }
                        $valueRestoration = $valueRestoration + $budget->value;
                        array_push($activities, array(
                            "id" => $budget->id,
                            "value" => $budget_value,
                            "length" => $budget_length,
                            "value_unit" => $budgetaction->budgetPriceMaterial->price,
                            "units" => $budgetaction->budgetPriceMaterial->units->name,
                            "hash_map" => $budget->hash_map,
                            "task_id" => $budget->task_id,
                            "process_id" => $id_process,
                            "action_material_id" => $budget->action_material_id,
                            "material_name" => $budgetaction->budgetPriceMaterial->name,
                            "action_name" => $budgetaction->action->name,
                            "action_id" => $budgetaction->action->id,
                            "area_referent" => $areaReferent,
                            "aislament" => $aislament,
                            "contribution" => $array_contribution,
                        ));
                    }
                    //--Acciones de Mantenimiento--//
                    if ($this->typeAction($typesActivities) == 1) {
                        $valueManteniment = $valueManteniment + $budget->value;
                        array_push($manteniment, array(
                            "id" => $budget->id,
                            "value" => $budget_value,
                            "length" => $budget_length,
                            "value_unit" => $budgetaction->budgetPriceMaterial->price,
                            "units" => $budgetaction->budgetPriceMaterial->units->name,
                            "hash_map" => $budget->hash_map,
                            "task_id" => $budget->task_id,
                            "process_id" => $id_process,
                            "action_material_id" => $budget->action_material_id,
                            "material_name" => $budgetaction->budgetPriceMaterial->name,
                            "action_name" => $budgetaction->action->name,
                            "action_id" => $budgetaction->action->id,
                            "area_referent" => $areaReferent,
                            "aislament" => $aislament,
                            "contribution" => $array_contribution,
                        ));
                    }
                }
            }
        }

        $info["conservation_area"] = $this->porcentualCal($conservationArea, $totalAreaConservation);
        $info["total_conservation_area"] = $totalAreaConservation;
        $info["percentage_total_conservation_area"] = 100;
        $info["activities_restoration"] = $activities;
        $info["value_total_restoration"] = $valueRestoration;
        $info["activities_manteniment"] = $manteniment;
        $info["value_total_manteniment"] = $valueManteniment;
        $info["activities_practices"] = $goodPractices;
        $info["value_total_practices"] = $valuePractices;
        //ACUERDO
        $info["agreement_subtotal"] = $valueRestoration + $valueManteniment + $valuePractices;
        $info["agreement_administration"] = ($info["agreement_subtotal"] * $administration) / 100; //20%
        $info["agreement_utility"] = ($info["agreement_subtotal"] * $utility) / 100; //5%
        $info["agreement_iva"] = ($info["agreement_utility"] * $iva) / 100; //19%
        $info["agreement_total"] = $info["agreement_subtotal"] + $info["agreement_administration"] + $info["agreement_utility"] + $info["agreement_iva"];
        //Zonificacion propuesta RLN
        $info['zone']['area']['ribera'] = $areaRArea;
        $info['zone']['area']['nacimiento'] = $areaNArea;
        $info['zone']['area']['ladera'] = $areaLArea;
        $info['zone']['porsent']['ribera'] = ($areaRArea * 100) / $sumAreaRNL;
        $info['zone']['porsent']['nacimiento'] = ($areaNArea * 100) / $sumAreaRNL;
        $info['zone']['porsent']['ladera'] = ($areaLArea * 100) / $sumAreaRNL;
        $info['zone']['arear_total'] = $sumAreaRNL;
        $info['zone']['porcet_total'] = 100;
        return $info;
    }

    public function associateforBudget($id_process)
    {

        $process = CvProcess::find($id_process);
        if (empty($process)) {

            return [
                "message" => "El procedimineto no existe en el sistema",
                "code" => 500,
            ];
        }

        $info = array();
        $activities = array();
        $associatedtypeOne = array();
        $associatedtypeTwo = array();
        $associatedtypeTree = array();
        $tasks = $process->processByTasks;

        foreach ($tasks as $tasky) {

            //--Busca tarea tipo medicion--//
            if ($tasky->task_sub_type_id > 3) {

                foreach ($tasky->budget as $budget) {

                    $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                    $action = CvActions::find($budgetaction->action->id);

                    //--Tipo de accion--//
                    $typesActivities = $budgetaction->action->types;

                    //--Acciones de Buenas practicas--//
                    if ($this->typeAction($typesActivities) == 3) {
                        $activitie_all = CvActionByActivity::where('action_id', $budgetaction->action->id)->get();
                        foreach ($activitie_all as $activitie) {
                            $contriAssociated = CvAssociatedContribution::where('project_activity_id', $activitie->activity_id);
                            if ($contriAssociated->exists()) {
                                //si hay inversionistas en la actividad, guarda en el array los asociados que invierten en la actividad
                                foreach ($contriAssociated->get() as $value) {
                                    //$activitie->activity_id == $value->project_activity_id &&
                                    if ($value->type == 1) {

                                        if(!empty($value->projectActivity->bycoordination)){
                                            if ($value->projectActivity->bycoordination->role_id == 9) {
                                                if ($this->insertDuplic($associatedtypeTree, $value->associated_id) == false) {
                                                    array_push($associatedtypeTree, array(
                                                        "id" => $value->associated_id,
                                                        "name" => CvAssociated::find($value->associated_id)->name,
                                                        "contribution_associated_id" => $value->id,
                                                        "budget_id" => $budget->id,
                                                        "action_name" => $action->name,
                                                    ));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //--Acciones de Restauracion--//
                    if ($this->typeAction($typesActivities) == 2) {
                        $activitie_all = CvActionByActivity::where('action_id', $budgetaction->action->id)->get();
                        foreach ($activitie_all as $activitie) {
                            $contriAssociated = CvAssociatedContribution::where('project_activity_id', $activitie->activity_id);
                            if ($contriAssociated->exists()) {
                                //si hay inversionistas en la actividad, guarda en el array los asociados que invierten en la actividad
                                foreach ($contriAssociated->get() as $value) {
                                    //$activitie->activity_id == $value->project_activity_id &&
                                    if ($value->type == 1) {
                                        if ($value->project_activity_id == $action->byActivite->activity_id) {
                                            if ($this->insertDuplic($associatedtypeOne, $value->associated_id) == false) {
                                                array_push($associatedtypeOne, array(
                                                    "id" => $value->associated_id,
                                                    "name" => CvAssociated::find($value->associated_id)->name,
                                                    "contribution_associated_id" => $value->id,
                                                    "budget_id" => $budget->id,
                                                    "action_name" => $action->name,
                                                ));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //--Acciones de Mantenimiento--//
                    if ($this->typeAction($typesActivities) == 1) {
                        $activitie_all = CvActionByActivity::where('action_id', $budgetaction->action->id)->get();
                        foreach ($activitie_all as $activitie) {
                            $contriAssociated = CvAssociatedContribution::where('project_activity_id', $activitie->activity_id);
                            if ($contriAssociated->exists()) {
                                //si hay inversionistas en la actividad, guarda en el array los asociados que invierten en la actividad
                                foreach ($contriAssociated->get() as $value) {
                                    //$activitie->activity_id == $value->project_activity_id &&
                                    if ($value->type == 1) {
                                        if ($value->project_activity_id == $action->byActivite->activity_id) {
                                            if ($this->insertDuplic($associatedtypeTwo, $value->associated_id) == false) {
                                                array_push($associatedtypeTwo, array(
                                                    "id" => $value->associated_id,
                                                    "name" => CvAssociated::find($value->associated_id)->name,
                                                    "contribution_associated_id" => $value->id,
                                                    "budget_id" => $budget->id,
                                                    "action_name" => $action->name,
                                                ));
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
        $info["associate_restoration"] = $associatedtypeOne; //tipe1
        $info["associate_manteniment"] = $associatedtypeTwo; //tipe2
        $info["associate_practices"] = $associatedtypeTree; //tipe3
        return $info;
    }

    public function shearOriginResource($id_process)
    {
        $info = array();
        $origintypeOne = array();
        $origintypeTwo = array();
        $origintypeTree = array();
        $valTotal = 0;
        $origin = CvOriginResource::where('process_id', $id_process);
        if ($origin->exists()) {
            foreach ($origin->get() as $value) {
                
                $value_total = $value->value; // 0
                /*  $origen_asociated = CvOriginResource::where('process_id', $id_process)->where('associated_id',$value->associated_id);
                if ($origen_asociated->count() > 1){
                foreach ($origen_asociated->get() as $associatedoll){
                $value_total=$value_total+$associatedoll->value;
                }
                }else{
                $value_total=$value->value;
                }*/

                $administration = $value->administration;
                $iva = $value->iva;
                $utility = $value->utility;

                $val20 = ($value_total / 100) * $administration;
                $val5 = ($value_total / 100) * $iva;
                $val19 = ($value_total / 100) * $utility;
                $valSubTotal = $val19 + $val20 + $val5 + $value_total;
                $valTotal = $valTotal + $valSubTotal;
                $all_contrubution = $value->detailOriginResource;
                foreach ($all_contrubution as $contri) {
                    $contri['associated_name'] = CvAssociated::find($contri->associated_id)->name;
                }
                if ($value->type_task == 1) {
                    array_push($origintypeOne, array(
                        "type_task" => $value->type_task,
                        "value" => $value_total,
                        "id_associated" => $value->associated_id,
                        "value_20" => $val20,
                        "value_5" => $val5,
                        "value_19" => $val19,
                        "value_total" => $valSubTotal,
                        "contributions" => $all_contrubution,

                    ));
                } elseif ($value->type_task == 2) {
                    array_push($origintypeTwo, array(
                        "type_task" => $value->type_task,
                        "value" => $value_total,
                        "id_associated" => $value->associated_id,
                        "value_20" => $val20,
                        "value_5" => $val5,
                        "value_19" => $val19,
                        "value_total" => $valSubTotal,
                        "contributions" => $all_contrubution,

                    ));
                } elseif ($value->type_task == 3) {
                    array_push($origintypeTree, array(
                        "type_task" => $value->type_task,
                        "value" => $value_total,
                        "id_associated" => $value->associated_id,
                        "value_20" => $val20,
                        "value_5" => $val5,
                        "value_19" => $val19,
                        "value_total" => $valSubTotal,
                        "contributions" => $all_contrubution,
                    ));
                }
            }
        }

        $info["origin_restoration"] = $origintypeOne; //tipe1
        $info["origin_manteniment"] = $origintypeTwo; //tipe2
        $info["origin_practices"] = $origintypeTree; //tipe3
        $info["origin_value_total"] = $valTotal;
        return $info;
    }

    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function originOfResources(Request $allbudget_associates)
    {

        foreach ($allbudget_associates->all() as $key => $budget_associates) {
            //Validar presupuesto
            $contributions = CvAssociatedContribution::find($budget_associates['contribution_associated_id']);
            if (!$contributions) {
                return [
                    "message" => "la contribucion no existe",
                    "code" => 500,
                ];
            }
            if ($contributions->balance <= $budget_associates['value']) {
                if ($contributions->committed_balance <= 0 || $contributions->committed_balance == null) {
                    $new_balance = $contributions->balance;
                } else {
                    $new_balance = $contributions->committed_balance;
                }
                return [
                    "message" => "El saldo disponible es de  " . $new_balance . ", no es suficiente para el presupuesto asignado",
                    "code" => 500,
                ];
            }
        }

        $this->deleteAllOriginDetail($allbudget_associates);

        foreach ($allbudget_associates->all() as $budget_associates) {
            $contributions = CvAssociatedContribution::find($budget_associates['contribution_associated_id']);
            $validate = CvOriginResource::where('budget_id', $budget_associates['budget_id']);
            if ($validate->exists()) {
                $resources = CvOriginResource::find($validate->first()->id);

                $resources_detail = new CvDetailOriginResource();
                $resources_detail->budget_id = $budget_associates['budget_id'];
                $resources_detail->origin_id = $resources->origin_id;
                $resources_detail->contribution_id = $budget_associates['contribution_associated_id'];

                $resources->value = $resources->value + $budget_associates['value'];
                $resources->save();
            } else {
                $resources = $this->createNewOriginResource($budget_associates);
                $resources_detail = new CvDetailOriginResource();
                $resources_detail->budget_id = $budget_associates['budget_id'];
                $resources_detail->contribution_id = $budget_associates['contribution_associated_id'];
            }
            $resources_detail->associated_id = $budget_associates['associated_id'];
            $resources_detail->origin_id = $resources->id;
            $resources_detail->user_id = $this->userLoggedInId();
            $resources_detail->value = $budget_associates['value'];
            $ultimate_balance = $contributions->committed_balance;
            $contributions->committed = $contributions->committed + $budget_associates['value'];
            $contributions->committed_balance = $contributions->balance - $contributions->committed;
            if ($contributions->committed_balance < 0) {
                return [
                    "message" => "El saldo disponible es de  " . $ultimate_balance . ", no es suficiente para el presupuesto asignado",
                    "code" => 500,
                ];
            }
            $contributions->save();
            $resources_detail->ultimate_committed = $budget_associates['value'];
            $resources_detail->save();
        }
        return [
            "message" => "Registro de origen de los recursos completado",
            "code" => 200,
        ];
    }

    public function budgetContractorRestoration($id_process)
    {

        $process = CvProcess::find($id_process);
        if (empty($process)) {

            return [
                "message" => "El procedimineto no existe en el sistema",
                "code" => 500,
            ];
        }

        $info = array();
        $activities = array();
        $manteniment = array();
        $goodPractices = array();
        $conservationArea = array();
        $associated = array();
        $tasks = $process->processByTasks;
        $valueRestoration = 0;
        $valueManteniment = 0;
        $valuePractices = 0;
        $areaReferent = 0;
        $totalAreaConservation = 0;
        $aislament = "";

        foreach ($tasks as $tasky) {

            $json = json_decode($tasky->property->info_json_general, true);
            $info["basin_name"] = $tasky->property->property_name;
            $info["property_priority"] = $json['property_correlation']; //Cambiar property_correlation a property_priority cuando se cree el campo en el front
            $info["municipality"] = $json['municipality'];

            //--Busca tarea tipo medicion--//
            if ($tasky->task_sub_type_id >= 14) {

                $taskgeojson = $tasky->geoJson;
                $geojson = json_decode($taskgeojson[0]->geojson, true);

                //--Ciclo para mapa--//
                $hash = "";

                foreach ($tasky->budget as $detail_budget) {

                    //--tipo de geometria del mapa--//
                    $totalAreaConservation = $totalAreaConservation + $detail_budget->length;
                    array_push($conservationArea, array(
                        "action" => $detail_budget->actionsMaterials->action->name,
                        "area" => $detail_budget->length,
                        "percentage" => $detail_budget->length,
                    ));
                    //--Hash para validar el mapa almacenado en el presupuesto--//
                    $hash = $detail_budget->hash_map;
                }
                foreach ($tasky->budget as $budget) {

                    //Validar el mapa almacenado en el presupuesto--//
                    if ($hash == $budget->hash_map) {
                        $areaReferent = $budget->hash_map;
                    }

                    $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                    //--Tipo de aislamiento--//
                    $typesActivities = $budgetaction->action->types;
                    foreach ($typesActivities as $typeAislament) {
                        if ($typeAislament->id == 2) {
                            $aislament = $typeAislament->name;
                        } elseif ($typeAislament->id == 3) {
                            $aislament = $typeAislament->name;
                        }
                    }
                    if ($budget->length == 0) {
                        $budget_length = 1;
                    } else {
                        $budget_length = $budget->length;
                    }
                    //--Acciones de Buenas practicas--//
                    if ($this->typeAction($typesActivities) == 3) {
                        if (!empty($budget->budgetContractor)) {
                            $valuePractices = $valuePractices + $budget->budgetContractor->price_contractor;
                            array_push($goodPractices, array(
                                "id" => $budget->budgetContractor->id,
                                "value" => $budget->budgetContractor->price_contractor,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget_length,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }
                    }
                    //--Acciones de Restauracion--//
                    if ($this->typeAction($typesActivities) == 2) {
                        if (!empty($budget->budgetContractor)) {

                            $valueRestoration = $valueRestoration + $budget->budgetContractor->price_contractor;
                            array_push($activities, array(
                                "id" => $budget->budgetContractor->id,
                                "value" => $budget->budgetContractor->price_contractor,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget_length,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }}
                    //--Acciones de Mantenimiento--//
                    if ($this->typeAction($typesActivities) == 1) {
                        if (!empty($budget->budgetContractor)) {
                            $valueManteniment = $valueManteniment + $budget->budgetContractor->price_contractor;
                            array_push($manteniment, array(
                                "id" => $budget->budgetContractor->id,
                                "value" => $budget->budgetContractor->price_contractor,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget_length,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }
                    }
                }

                /**
                 * Guardar informacion del presupuesto
                 */
                $administration = $budget->administration; 
                $iva = $budget->iva;
                $utility =  $budget->utility;

            }
        }

        $info["conservation_area"] = $this->porcentualCal($conservationArea, $totalAreaConservation);
        $info["total_conservation_area"] = $totalAreaConservation;
        $info["percentage_total_conservation_area"] = 100;
        $info["activities_restoration"] = $activities;
        $info["value_total_restoration"] = $valueRestoration;
        $info["activities_manteniment"] = $manteniment;
        $info["value_total_manteniment"] = $valueManteniment;
        $info["activities_practices"] = $goodPractices;
        $info["value_total_practices"] = $valuePractices;
        //ACUERDO
        $info["agreement_subtotal"] = $valueRestoration + $valueManteniment + $valuePractices;
        $info["agreement_administration"] = ($info["agreement_subtotal"] * $administration) / 100; //20%
        $info["agreement_utility"] = ($info["agreement_subtotal"] * $utility) / 100; //5%
        $info["agreement_iva"] = ($info["agreement_utility"] * $iva) / 100; //19%
        $info["agreement_total"] = $info["agreement_subtotal"] + $info["agreement_administration"] + $info["agreement_utility"] + $info["agreement_iva"];

        return $info;
    }

    public function budgetExecutionRestoration($id_process)
    {

        $process = CvProcess::find($id_process);
        if (empty($process)) {

            return [
                "message" => "El procedimineto no existe en el sistema",
                "code" => 500,
            ];
        }

        $info = array();
        $activities = array();
        $manteniment = array();
        $goodPractices = array();
        $conservationArea = array();
        $associated = array();
        $tasks = $process->processByTasks;
        $valueRestoration = 0;
        $valueManteniment = 0;
        $valuePractices = 0;
        $areaReferent = 0;
        $totalAreaConservation = 0;
        $aislament = "";

        /**
        * Inicializacion variables de porcentajes dinamicas a partir del budget
        */
        $administration = 0;
        $iva = 0;
        $utility = 0;

        foreach ($tasks as $tasky) {

            $json = json_decode($tasky->property->info_json_general, true);
            $info["basin_name"] = $tasky->property->property_name;
            $info["property_priority"] = $json['property_correlation']; //Cambiar property_correlation a property_priority cuando se cree el campo en el front
            $info["municipality"] = $json['municipality'];

            //--Busca tarea tipo medicion--//
            if ($tasky->task_sub_type_id >= 14) {

                $taskgeojson = $tasky->geoJson;
                $geojson = json_decode($taskgeojson[0]->geojson, true);

                //--Ciclo para mapa--//
                $hash = "";
                foreach ($tasky->budget as $detail_budget) {
                    //--tipo de geometria del mapa--//
                    $totalAreaConservation = $totalAreaConservation + $detail_budget->length;
                    array_push($conservationArea, array(
                        "action" => $detail_budget->actionsMaterials->action->name,
                        "area" => $detail_budget->length,
                        "percentage" => $detail_budget->length,
                    ));
                    //--Hash para validar el mapa almacenado en el presupuesto--//
                    $hash = $detail_budget->hash_map;
                }
                foreach ($tasky->budget as $budget) {

                    /**
                     * Variables de porcentajes dinamicas a partir del budget
                     */
                    $administration = $budget->administration;
                    $iva = $budget->intval;
                    $utility = $budget->utility;

                    //Validar el mapa almacenado en el presupuesto--//
                    if ($hash === $budget->hash_map) {

                        $areaReferent = $budget->hash_map;
                    }

                    $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                    //--Tipo de aislamiento--//
                    $typesActivities = $budgetaction->action->types;
                    foreach ($typesActivities as $typeAislament) {
                        if ($typeAislament->id == 2) {
                            $aislament = $typeAislament->name;
                        } elseif ($typeAislament->id == 3) {
                            $aislament = $typeAislament->name;
                        }
                    }

                    //--Acciones de Buenas practicas--//
                    if ($this->typeAction($typesActivities) == 3) {
                        if (!empty($budget->budgetContractor->budgetExecution)) {
                            $valuePractices = $valuePractices + $budget->budgetContractor->budgetExecution->price_execution;
                            array_push($goodPractices, array(
                                "id" => $budget->budgetContractor->budgetExecution->id,
                                "value" => $budget->budgetContractor->budgetExecution->price_execution,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget->budgetContractor->budgetExecution->shape_leng,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }
                    }
                    //--Acciones de Restauracion--//
                    if ($this->typeAction($typesActivities) == 2) {

                        if (!empty($budget->budgetContractor->budgetExecution)) {
                            $valueRestoration = $valueRestoration + $budget->budgetContractor->budgetExecution->price_execution;
                            array_push($activities, array(
                                "id" => $budget->budgetContractor->budgetExecution->id,
                                "value" => $budget->budgetContractor->budgetExecution->price_execution,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget->budgetContractor->budgetExecution->shape_leng,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }}
                    //--Acciones de Mantenimiento--//
                    if ($this->typeAction($typesActivities) == 1) {
                        if (!empty($budget->budgetContractor->budgetExecution)) {
                            $valueManteniment = $valueManteniment + $budget->budgetContractor->budgetExecution->price_execution;
                            array_push($manteniment, array(
                                "id" => $budget->budgetContractor->budgetExecution->id,
                                "value" => $budget->budgetContractor->budgetExecution->price_execution,
                                "value_unit" => $budget->budgetContractor->budget_contractor,
                                "length" => $budget->budgetContractor->budgetExecution->shape_leng,
                                "units" => $budgetaction->budgetPriceMaterial->units->name,
                                "hash_map" => $budget->hash_map,
                                "task_id" => $budget->task_id,
                                "action_material_id" => $budget->action_material_id,
                                "material_name" => $budgetaction->budgetPriceMaterial->name,
                                "action_name" => $budgetaction->action->name,
                                "action_id" => $budgetaction->action->id,
                                "area_referent" => $areaReferent,
                                "aislament" => $aislament,
                            ));
                        }
                    }
                }
            }
        }

        $info["conservation_area"] = $this->porcentualCal($conservationArea, $totalAreaConservation);
        $info["total_conservation_area"] = $totalAreaConservation;
        $info["percentage_total_conservation_area"] = 100;
        $info["activities_restoration"] = $activities;
        $info["value_total_restoration"] = $valueRestoration;
        $info["activities_manteniment"] = $manteniment;
        $info["value_total_manteniment"] = $valueManteniment;
        $info["activities_practices"] = $goodPractices;
        $info["value_total_practices"] = $valuePractices;
        //ACUERDO
        $info["agreement_subtotal"] = $valueRestoration + $valueManteniment + $valuePractices;
        $info["agreement_administration"] = ($info["agreement_subtotal"] * $administration) / 100; //20%
        $info["agreement_utility"] = ($info["agreement_subtotal"] * $utility) / 100; //5%
        $info["agreement_iva"] = ($info["agreement_utility"] * $iva) / 100; //19%
        $info["agreement_total"] = $info["agreement_subtotal"] + $info["agreement_administration"] + $info["agreement_utility"] + $info["agreement_iva"];

        return $info;
    }

    private function typeAction($param)
    {

        foreach ($param as $value) {
            if ($value->id == 4 || $value->id == 5 || $value->id == 6 || $value->id == 7 || $value->id == 8 || $value->id == 9) {
                return 2; //RESTAURACION
            } elseif ($value->id == 1) {
                return 1; //MANTENIMIENTO
            } else {
                return 3; //BUENAS PRACTICAS
            }
        }
    }

    private function porcentualCal($array, $total)
    {
        $porcent = 0;
        for ($i = 0; $i < count($array); $i++) {
            $area = $array[$i]["area"] * 100;
            if ($total != 0) {
                $porcent = $area / $total;
            }
            $array[$i]["percentage"] = $porcent;
        }
        return ($array);
    }

    private function insertDuplic($associated, $id)
    {

        if (count($associated) >= 1) {

            for ($i = 0; $i < count($associated); $i++) {
                if ($associated[$i]["id"] == $id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $budget_associates
     * @return CvOriginResource
     */
    private function createNewOriginResource($budget_associates)
    {
        $type_task = 3;
        $alltypes = CvBudget::find($budget_associates['budget_id'])->actionsMaterials->actionOne->types;
        foreach ($alltypes as $ac_type) {
            if ($ac_type->id == 5 || $ac_type->id == 6 || $ac_type->id == 7) {
                $type_task = 2;
            }
            if ($ac_type->id == 8 || $ac_type->id == 9) {
                $type_task = 1;
            }
        }
        $resources = new CvOriginResource();
        $resources->type_task = $type_task;
        $resources->value = $budget_associates['value'];
        $resources->budget_id = $budget_associates['budget_id'];
        $resources->process_id = $budget_associates['process_id'];
        $resources->save();
        return $resources;
    }

    /**
     * @param Request $allbudget_associates
     * @return array
     */
    public function deleteAllOriginDetail(Request $allbudget_associates)
    {
        foreach ($allbudget_associates->all() as $key => $budget_associates) {
            $validate_detail = CvDetailOriginResource::where('budget_id', $budget_associates['budget_id']);
            if ($validate_detail->exists()) {
                foreach ($validate_detail->get() as $origin_one) {
                    $contributions = CvAssociatedContribution::find($budget_associates['contribution_associated_id']);
                    $contributions->committed = $contributions->committed - $origin_one->ultimate_committed;
                    $contributions->save();
                    $origin_one->delete();
                }
            }
        }

    }

    /**=======================================================
     * Actualizar porcentajes de presupuesto por procedimiento
     *========================================================*/

     public function updateBudgetByProcess(Request $request) {
        
        $process = CvProcess::find($request->process_id);
        if (empty($process)) {
            return response()->json(['message' => 'El procedimineto no existe en el sistema.', 'code' => 409], 409);
        }

        $tasks = $process->processByTasks;

        if (empty($tasks)) {
            return response()->json(['message' => 'El procedimiento no cuenta con tareas en el sistema.', 'code' => 409], 409);
        }


        foreach ($tasks as $task) {

            //*** Obtener tarea de medicion ***//
            if ($task->task_type_id && $task->task_sub_type_id >= 4) {

                if (empty($task->budget)) {
                    return response()->json(['message' => 'El procedimiento con la tarea (' . $task->id . ') de medición no cuenta aún con presupuesto.', 'code' => 409], 409);
                }  
                foreach ($task->budget as $budget) {

                    /**=================================================================================================
                    * Actualizar todas las variables de porcentaje para los presupuestos de una tarea y un procedimiento
                    *===================================================================================================*/

                    //*** Administracion ***//
                    if ($budget->administration != $request->administration && $request->administration != 0) {
                        $budget->administration = $request->administration;
                    }
                    
                    //*** Utilidad ***//
                    if ($budget->utility != $request->utility && $request->utility != 0) {
                        $budget->utility = $request->utility;
                    }

                    //*** Iva ***//
                    if ($budget->iva != $request->iva && $request->iva != 0) {
                        $budget->iva = $request->iva;
                    }

                    $budget->save();
                }
                return response()->json(['message' => 'Actualización exitosa.', 'code' => 200], 200);
            }
        }
     }

}
