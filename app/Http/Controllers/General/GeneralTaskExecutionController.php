<?php

namespace App\Http\Controllers\General;

use App\CvAssociatedContribution;
use App\CvBackupTaskExecution;
use App\CvBudgetByBudgetContractor;
use App\CvBudgetByBudgetExcution;
use App\CvComment;
use App\CvCommentByOtherTask;
use App\CvOriginResource;
use App\CvTask;
use App\CvTaskExecutionGeoMap;
use App\CvTaskOpen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvPoolActionByUser;
use App\CvProcess;
use App\CvBudget;
use App\CvActions;
use App\CvBudgetPriceMaterial;
use App\User;
use App\CvTaskExecution;
use App\CvTaskExecutionUser;
use App\CvPoolProcess;
use Carbon\Carbon;
use App\CvPool;
use DB;

class GeneralTaskExecutionController extends Controller {

    //--- Consultar las acciones con su respectivo contratista ---//

    public function consultActionsContractor() {

        $actionsByUserContractor = CvPoolActionByUser::get();

        foreach ($actionsByUserContractor as $item) {
            $namePredio = null;
            $numPoligon = 'n/a';
            $hash = null;

            $process = CvProcess::find($item->poolProcess->process_id);
            $budget = CvBudget::find($item->poolProcess->budget_id);
            $actionsMaterials = $budget->actionsMaterials;
            $action = CvActions::find($actionsMaterials->action_id);
            $material = CvBudgetPriceMaterial::find($actionsMaterials->budget_prices_material_id);
            $user = User::find($item->user_id);
            $budget_contractor = CvBudgetByBudgetContractor::where('budget_id', $budget->id)->where('contractor_id', $user->id)->first();
            //--- Respuesta personalizada ---//


            $modelTaskExecution = CvTaskExecutionUser::where('pool_contractor_id', $item->id)->get();

            $hash = $budget->hash_map;

            $numPoligon = $this->getNumPolygon($modelTaskExecution, $hash, $numPoligon);

            $namePredio = $process->potentialProperty->property_name;

            $item["user_name"] = $user->name;
            $item["process_id"] = $process->id;
            $item["process_name"] = $process->name;
            $item["action_name"] = $action->name;
            $item["material_name"] = $material->name;
            $item["unit_measurement"] = $material->units->name;
            $item["pool_name"] = CvPool::find($item->poolProcess->pool_id)->name;
            $item["value_contractor"] = $budget_contractor->budget_contractor;
            $item["propertyName"] = $namePredio;
            $item["numPolygon"] = $numPoligon;
        }

        return $actionsByUserContractor;
    }

    //--- Registrar tarea de ejecucion ---//

    public function registerTaskExecution(Request $request) {

        //--- Transaccion ---//
        DB::beginTransaction();

        $poolActionByUser = CvPoolActionByUser::find($request->pool_contractor_id);

        if (empty($poolActionByUser)) {
            return [
                "message" => "No existe información del presupuesto en una bolsa para el actual usuario",
                "code" => 200
            ];
        }

        // return $request->all();

        $taskExecution = new CvTaskExecution();

        $taskExecution->title = $request->title;
        $taskExecution->description = $request->description;
        $taskExecution->date_start = $request->startdate;
        $taskExecution->date_end = $request->deadline;
        $taskExecution->task_status_id = 2;
        $taskExecution->task_open_sub_type_id = 9;
        $taskExecution->pool_actions_contractor_id = $request->pool_contractor_id;

        if ($taskExecution->save()) {

            //--- Registrar informacion de en la pivote de usuarios por tarea de ejecucion ---//

            $countUsersTotal = 0;

            foreach ($request->users as $item_user) {

                $taskExecutionUser = new CvTaskExecutionUser();
                $taskExecutionUser->task_id = $taskExecution->id;
                $taskExecutionUser->user_id = $item_user;
                $taskExecutionUser->pool_contractor_id = $request->pool_contractor_id;

                if ($taskExecutionUser->save()) {
                    $countUsersTotal++;
                }
            }

            //--- Validar que los registros que se almacenaron sean iguales a los que se enviaron ---//
            if ($countUsersTotal == count($request->users)) {
                DB::commit();

                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            } else {
                DB::rollback();
            }
        }
    }

    //--- Actualizar tarea de ejecucion ---//

    public function updateTaskExecution(Request $request, $id) {

        $taskExecution = CvTaskExecution::find($id);

        if (empty($taskExecution)) {

            return [
                "message" => "La tarea de ejecucion no existe en el sistema",
                "code" => 500
            ];
        }

        $poolActionByUser = CvPoolActionByUser::find($request->pool_contractor_id);

        if (empty($poolActionByUser)) {
            return [
                "message" => "No existe información del presupuesto en una bolsa para el actual usuario",
                "code" => 200
            ];
        }

        $taskExecution->title = $request->title;
        $taskExecution->description = $request->description;
        $taskExecution->date_start = $request->startdate;
        $taskExecution->date_end = $request->deadline;
        $taskExecution->pool_actions_contractor_id = $request->pool_contractor_id;

        if ($taskExecution->save()) {
            return [
                "message" => "Registro actualizado",
                "code" => 200
            ];
        }
    }

    //--- Aprobar tarea de ejecucion ---//

    public function approvedTaskExecution($id) {

        $taskExecution = CvTaskExecutionUser::where("task_id", $id)->first();

        if (empty($taskExecution)) {
            return [
                "message" => "La tarea de ejecucion no existe en el sistema",
                "code" => 500
            ];
        }

        $tempTask = $taskExecution->task_id;
        $tempPoolContractor = $taskExecution->pool_contractor_id;

        //--- Eliminar tarea de ejecucion ---//

        $taskExecution->delete();

        //--- Asignar la tarea al rol de coordinador ---//

        $coordinationRole = User::whereIn("role_id", [3])->get();

        $state = 0;

        foreach ($coordinationRole as $user) {

            //--- Actualizar el estado de la tarea ---//

            $updateTaskExecution = CvTaskExecution::find($tempTask);

            $updateTaskExecution->task_status_id = 1;

            $updateTaskExecution->save();

            //--- Registrar la tarea a los usuarios con rol de coordinador ---//

            $state = 1;

            $taskExecutionNew = new CvTaskExecutionUser();

            $taskExecutionNew->user_id = $user->id;
            $taskExecutionNew->task_id = $tempTask;
            $taskExecutionNew->pool_contractor_id = $tempPoolContractor;

            $taskExecutionNew->save();
        }

        if ($state == 1) {

            return [
                "message" => "Tarea aprobada",
                "code" => 200
            ];
        }
    }

    //*** Servicio para consultar el porcentaje de las tareas finalizadas por el contratista ***//

    public function percentageTaskExecution($id) {

        $process = CvProcess::find($id);

        if (empty($process)) {
            return [
                "message" => "El procedimiento no existe en el sistema",
                "code" => 500
            ];
        }

        $infoBudgetProcess = [];

        if (isset($process->processByTasks)) {
            foreach ($process->processByTasks as $task) {
                if ($task->task_type_id == 1) {

                    $budgetData = CvBudget::where("task_id", $task->id)->get();
                    foreach ($budgetData as $budget) {
                        array_push($infoBudgetProcess, $budget->id);
                    }
                }
            }
        }

        // --- Encontrar cuales presupuestos tiene bolsa contratista --- //

        $taskExecution = CvTaskExecutionUser::where("user_id", $this->userLoggedInId())->get();

        $actionsBudgetPool = [];

        foreach ($taskExecution as $execution) {

            $poolActionUser = CvPoolActionByUser::find($execution->taskExecution->pool_actions_contractor_id);
            $poolAction = CvPoolProcess::find($poolActionUser->pool_by_process_id);
            array_push($actionsBudgetPool, $poolAction->budget_id);
        }

        $arrayUniqueBudgetPool = array_unique($actionsBudgetPool);

        $percentage = 0;

        if (count($infoBudgetProcess) != 0 && count($arrayUniqueBudgetPool) != 0) {
            $percentage = ((count($arrayUniqueBudgetPool) * 100) / count($infoBudgetProcess));
        }

        return [
            "data" => round($percentage, 2),
            "code" => 200
        ];
    }

    //*** Consultar la tarea de ejecucion ***//

    public function consultTaskExecution() {

        if ($this->userLoggedInRol() == 9 || $this->userLoggedInRol() == 15) {
            $taskExecution = CvTaskExecutionUser::get();
        } else {
            $taskExecution = CvTaskExecutionUser::where("user_id", $this->userLoggedInId())->get();
        }

        $taskExecution = CvTaskExecutionUser::get();
        
        if (empty($taskExecution)) {

            return [
                "message" => "El usuario no cuenca con tareas de ejecucion",
                "code" => 200
            ];
        }

        $info = [];

        foreach ($taskExecution as $execution) {

            $execution["user_id"] = $execution->user_id;
            $execution->taskExecution["task_sub_type_name"] = $execution->taskExecution->subtypes->name;

            array_push($info, $execution->taskExecution);
        }

        return $info;
    }

    //*** Consultar tarea de ejecucion en especifico ***//

    public function consultTaskExecutionSpecific($id) {

        $execution = CvTaskExecution::find($id);

        if (empty($execution)) {
            return [
                "message" => "La tarea de ejecucion no existe en el sistema",
                "code" => 500
            ];
        }

        $process = $execution->taskExecutionByUser->actionByUserContractor->poolProcess;
        $contractor = $execution->taskExecutionByUser->actionByUserContractor;
        $info = [
            "id" => $execution->id,
            "title" => $execution->title,
            "description" => $execution->description,
            "date_start" => $execution->date_start,
            "date_end" => $execution->date_end,
            "pool_actions_contractor_id" => $execution->pool_actions_contractor_id,
            "task_status_id" => $execution->task_status_id,
            "created_at" => Carbon::now()->format($execution->created_at),
            "updated_at" => Carbon::now()->format($execution->updated_at)
        ];

        //*** Personalizar respuesta ***//

        $info["user_id"] = $execution->taskExecutionByUser->user_id;
        $info["user_name"] = User::find($execution->taskExecutionByUser->user_id)->name;
        $info["process_id"] = $process->Process->id;
        $info["budget_id"] = $process->Budget->id;
        $info["sub_type_id"] = $execution->task_open_sub_type_id;
        $info["sub_type_name"] = $execution->subtypes->name;
        $info["user_id_contractor"] = $contractor->user_id;
        $info["contractor_name"] = User::find($contractor->user_id)->name;

        return $info;
    }

    public function loadMapTaskExecution(Request $dates) {

        $task = CvTaskExecution::find($dates->task_id);
        if (empty($task)) {
            return [
                "message" => "La tarea de ejecucion no existe en el sistema",
                "code" => 500
            ];
        }


        $geoMap = new CvTaskExecutionGeoMap();
        $geoMap->mapjson = json_encode($dates->geojson, true);
        $geoMap->task_execution_id = $dates->task_id;

        $backup = new CvBackupTaskExecution();
        $assign = $task->taskExecutionByUser;

        $backup->to_user = $assign->user_id;
        $assign->user_id = $this->userLoggedInId();
        $backup->go_user = $assign->user_id;

        $backup->to_subtype = $task->task_open_sub_type_id;
        if ($task->task_open_sub_type_id == 9) {
            //Si la tarea biene de seguimiento y va a coordinacion
            $geoMap->type = 1;
            $task->task_open_sub_type_id = 10;
        }
        if ($task->task_open_sub_type_id == 11) {
            //Si la tarea biene de sig y va a coordinacion
            $geoMap->type = 2;
            $task->task_open_sub_type_id = 12;
        } else {
            $geoMap->type = 3;
        }
        $backup->go_subtype = $task->task_open_sub_type_id;

        $backup->mapjson = $geoMap->mapjson;
        $backup->type = $geoMap->type;
        $backup->task_execution_id = $geoMap->task_execution_id;

        //Calcula presupuesto por accion dado por el contratista
        $budget_contractor = $task->taskExecutionByUser->actionByUserContractor->poolProcess->Budget;
        $leng = 0;
        $validate = 0;
        $mapsall = json_decode($dates->geojson, true);
        foreach ($mapsall['features'] as $geometri) {
            if (array_key_exists('Longitud_M', $geometri['properties'])) {
                $validate = 1;
                $leng = $leng + $geometri['properties']['Longitud_M'];
            } elseif (array_key_exists('AREA_HA', $geometri['properties'])) {
                $validate = 1;
                $leng = $leng + $geometri['properties']['AREA_HA'];
            }
        }
        if ($validate != 0) {
            //presupuesto total de execuion
            $budget_execution_action_total = $leng * $budget_contractor->budgetContractor->budget_contractor;

            $validate = CvBudgetByBudgetExcution::where('task_execution_id', $task->id);
            if ($validate->exists()) {
                $budget_execution_action = CvBudgetByBudgetExcution::find($validate->first()->id);
            } else {
                $budget_execution_action = new CvBudgetByBudgetExcution();
            }

            $budget_execution_action->shape_leng = $leng;
            $budget_execution_action->price_execution = $budget_execution_action_total;
            $budget_execution_action->task_execution_id = $task->id;
            $budget_execution_action->budget_contractor_id = $budget_contractor->budgetContractor->id;

            //Consulta el id Budget
            if (empty($budget_contractor->originResource)) {
                return [
                    "message" => "El presupuesto no tiene origen de los recursos",
                    "code" => 500
                ];
            }
            //TODO Generador de tercer presupuesto
            /*       $commandAnController = CvAssociatedContribution::find($budget_contractor->originResource->contribution_id);
              $commandAnController->committed = $commandAnController->committed - $budget_contractor->originResource->ultimate_committed;

              if ($commandAnController->committed <=  0){
              $commandAnController->committed = 0;
              }

              $commandAnController->committed = $commandAnController->committed + $budget_execution_action->price_execution;

              $origin=CvOriginResource::find($budget_contractor->originResource->id);
              $origin->ultimate_committed=$budget_execution_action->price_execution;

              $commandAnController->committed_balance= $commandAnController->balance -  $commandAnController->committed;

              if ( $commandAnController->committed_balance < 0){
              return [
              "message" => "El presupuesto no alcanza en lo disponible del cuadro de mando",
              "code" => 500
              ];
              } */

            //  $origin->save();
            // $commandAnController->save();
            $budget_execution_action->save();
            $assign->save();
            $backup->save();
        }
        $geoMap->save();
        return [
            "message" => "Geometria almacenada",
            "code" => 200
        ];
    }

    public function getGeoMapTaskExecution($task_id) {
        $task = CvTaskExecution::find($task_id);

        if (empty($task)) {
            return [
                "message" => "La tarea de ejecucion no existe",
                "code" => 500
            ];
        }

        if (empty($task->geoMapLoad->last()->mapjson)) {
            return $task->geoMapLoad;
        }
        return json_decode($task->geoMapLoad->last()->mapjson, true);
    }

    public function getMapTaskExecution($task_id) {
        $task = CvTaskExecution::find($task_id);
        if (empty($task)) {
            return [
                "message" => "La tarea de ejecucion no existe en el sistema",
                "code" => 500
            ];
        }


        $measurement = $task->taskExecutionByUser->actionByUserContractor->poolProcess->Budget->task;
        $geoJson = json_decode($measurement->geoJsonOne->geojson, true);

        return $geoJson;
    }

    public function nextFlowTaskExecution(Request $dates) {
        $backup = new CvBackupTaskExecution();

        $task = CvTaskExecution::find($dates->task_id);

        //cambia subtipo

        $backup->to_subtype = $task->task_open_sub_type_id;
        $task->task_open_sub_type_id = $task->subtypes->go_to;
        $backup->go_subtype = $task->task_open_sub_type_id;

        //Si el subtipo es igual al id es por que no tiene un flujo correspondiente y otro servicio la debe saltar de subtipo
        if ($task->subtypes->go_to == $task->subtypes->id) {

            return [
                "message" => "La tarea no puedo continuar un flujo",
                "code" => 500
            ];
        }

        //cambia usuario
        $assign = $task->taskExecutionByUser;
        $backup->to_user = $assign->user_id;

        if ($task->subtypes->id == 13 || $task->subtypes->id == 9 || $task->subtypes->id == 11) {

            $assign->user_id = User::where('role_id', 9)->inRandomOrder()->first()->id;
        } else {

            $assign->user_id = $dates->user_id;
        }

        $backup->go_user = $assign->user_id;

        //Datos de la tarea de ejecucion
        if ($task->geoMapLoad->last()) {
            $backup->mapjson = ($task->geoMapLoad->last()->mapjson);
            $backup->type = $task->geoMapLoad->last()->type;
        } else {
            $backup->mapjson = "";
            $backup->type = 0;
        }

        $backup->task_execution_id = $task->id;


        //Almacena
        $task->save();
        $assign->save();
        $backup->save();

        return [
            "message" => "Asignado a otro usuario",
            "code" => 200
        ];
    }

    public function endTaskMeasurement($id_task) {
        $task = CvTaskExecution::find($id_task);

        if (empty($task)) {
            return [
                "message" => "La tarea de ejecucion no se existe",
                "code" => 500
            ];
        }

        if ($task->task_open_sub_type_id == 15) {
            $task->task_open_sub_type_id = 16;
            $task->save();
        } else {
            return [
                "message" => "La tarea de ejecucion no se encuentra en el subtipo correspondiente",
                "code" => 500
            ];
        }

        return [
            "message" => "Se ha finalizado la tarea de ejecucion",
            "code" => 200
        ];
    }

    public function validateSubtypeOn($task_id) {
        $task = CvTaskExecution::find($task_id);
        if ($task->task_open_sub_type_id == 16 || $task->task_open_sub_type_id == 15) {
            return 1;
        } else {
            $backup = CvBackupTaskExecution::where('task_execution_id', $task_id)->get();
            foreach ($backup as $detail) {
                if ($detail->to_subtype == 15 || $detail->to_subtype == 16) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * @param $modelTaskExecution
     * @param $hash
     * @param $numPoligon
     * @return mixed
     */
    private function getNumPolygon($modelTaskExecution, $hash, $numPoligon) {
        if (!$modelTaskExecution->isEmpty()) {
            $modelGeoMap = CvTaskExecutionGeoMap::where('task_execution_id', $modelTaskExecution[0]->id)->orderBy('id', 'desc')->first();
            if ($modelGeoMap !== null) {
                $arrGeoMap = json_decode(json_decode($modelGeoMap->mapjson), true);
                foreach ($arrGeoMap['features'] as $feature) {
                    if ($feature['properties']['hash'] === $hash) {
                        $numPoligon = $feature['properties']['POLIGONO'];
                    }
                }
            }
        }
        return $numPoligon;
    }

}
