<?php

namespace App\Http\Controllers;

use App\CvActionByActivity;
use App\CvActions;
use App\CvActivityCoordination;
use App\CvAssociatedContribution;
use App\CvBudget;
use App\CvBudgetActionMaterial;
use App\CvBudgetByBudgetContractor;
use App\CvBudgetByBudgetExcution;
use App\CvBudgetPriceMaterial;
use App\CvContract;
use App\CvContractorBudgetDetailOrigin;
use App\CvDetailOriginResource;
use App\CvOriginResource;
use App\CvOtherInfoContractor;
use App\CvPool;
use App\CvPoolActionByUser;
use App\CvPoolProcess;
use App\CvProcess;
use App\CvProjectActivity;
use App\CvProperty;
use App\CvTaskOpen;
use App\CvTaskOpenBudget;
use App\CvUnforeseenTariffContractor;
use App\Http\Controllers\General\GeneralContractorsController;
use App\Http\Controllers\Search\SearchAlgoliaController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PoolController extends Controller
{

    // *** Consultar todas las bolsas *** //

    public function index()
    {

        $pools = CvPool::get();

        $info = array();

        if (count($pools) > 0) {
            foreach ($pools as $pool) {
                array_push($info, $this->consultGeneralSpecificPool($pool));
            }

            return $info;
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function create()
    {
        abort(404);
    }

    // *** Registrar una nueva bolsa con procedimientos *** //

    public function store(Request $request)
    {
        foreach ($request->process as $process => $budget) {
            foreach ($budget as $dataBudget) {
                $poolProcessExists = CvPoolProcess::where("process_id", $process)->where("budget_id", $dataBudget);
                if ($poolProcessExists->exists()) {
                    return [
                        "message" => "Los presupuestos del procedimiento ya se encuentran registrados en otra bolsa",
                        "response_code" => 500,
                    ];
                }
            }
        }
        foreach ($request->task_proces as $process => $taskopen) {
            foreach ($taskopen as $dataOpen) {
                $poolProcess = new CvPoolProcess();
                //--- Validar que no existan presupuestos es una bolsa ya creada ---//
                $poolProcessExistsTask = CvPoolProcess::where("process_id", $process)->where("task_open_id", $dataOpen);
                if ($poolProcessExistsTask->exists()) {
                    return [
                        "message" => "Los presupuestos del procedimiento ya se encuentran registrados en otra bolsa",
                        "response_code" => 500,
                    ];
                }
            }
        }
        //--- Registrar bolsa ---//
        $pool = new CvPool();
        $pool->name = $request->name;
        $pool->consecutive = $this->consecutivePool();
        $pool->contract_id = $request->contract_type;
        //--- Registrar la relacion de procedimientos, bolsa y presupuesto ---//
        if ($pool->save()) {
            foreach ($request->process as $process => $budget) {
                foreach ($budget as $dataBudget) {
                    $poolProcess = new CvPoolProcess();
                    $poolProcess->pool_id = $pool->id;
                    $poolProcess->process_id = $process;
                    $poolProcess->budget_id = $dataBudget;
                    if ($poolProcess->save()) {
                        $status = 1;
                        // --- Guardar informacion del buscador --- //
                        $this->infoSearchPool($pool->id);
                    }
                }
            }
            foreach ($request->task_proces as $process => $taskopen) {
                foreach ($taskopen as $dataOpen) {
                    $poolProcess = new CvPoolProcess();
                    $poolProcess->pool_id = $pool->id;
                    $poolProcess->process_id = $process;
                    $poolProcess->task_open_id = $dataOpen;
                    if ($poolProcess->save()) {
                        $this->CommitedAssociatedIsTaskOpen($dataOpen);

                        // --- Guardar informacion del buscador --- //
                        $this->infoSearchPool($pool->id);
                    }
                }
            }
        }
        return [
            "message" => "Registro exitoso",
            "response_code" => 200,
        ];
    }

    // *** Consultar una bolsa en especifico *** //
    public function show($id)
    {

        $pool = CvPool::find($id);

        if (!empty($pool)) {

            $data = $this->consultGeneralSpecificPool($pool);

            $valothercamps = CvOtherInfoContractor::where('pool_id', $id)->exists();

            if ($valothercamps) {
                $data['other_camps'] = json_decode(CvOtherInfoContractor::where('pool_id', $id)->first()->infojson, true);
            } else {
                $data['other_camps'] = null;
            }

            return $data;
        } else {
            return [
                "message" => "La bolsa no existe en el sistema",
                "response_code" => 500,
            ];
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //
    public function edit($id)
    {
        abort(404);
    }

    // *** Actualizar una bolsa con procedimientos *** //
    public function update(Request $request, $id)
    {

        //--- Actualizar bolsa ---//

        $pool = CvPool::find($id);

        if (empty($pool)) {
            return [
                "message" => "La bolsa no existe en el sistema",
                "response_code" => 500,
            ];
        }

        $pool->name = $request->name;

        //--- Estado del registro ---//
        $status = 0;

        //--- Eliminar los registros de presupuestos y procedimientos de una bolsa para sobreescribirlos ---//

        $poolDeletePivot = CvPoolProcess::where("pool_id", $pool->id)->get();

        foreach ($poolDeletePivot as $poolPivot) {

            foreach ($request->process as $process => $budget) {

                foreach ($budget as $dataBudget) {

                    //--- Validar que la bolsa no tenga acciones por usuario ---//
                    $poolActionByUser = CvPoolActionByUser::where("pool_by_process_id", $poolPivot->id)->first();

                    if ($poolPivot->pool_id == $pool->id && $poolPivot->budget_id != $dataBudget) {

                        if (empty($poolActionByUser)) {
                            $poolPivot->delete();
                        }
                    }
                }
            }
        }

        //--- Actualizar la relacion de procedimientos, bolsa y presupuesto ---//

        if ($pool->save()) {

            foreach ($request->process as $process => $budget) {

                foreach ($budget as $dataBudget) {

                    $poolProcess = new CvPoolProcess();
                    $poolProcess->pool_id = $pool->id;
                    $poolProcess->process_id = $process;
                    $poolProcess->budget_id = $dataBudget;

                    //--- Verificar si los procedimientos y presupuestos ya se encuentran registrados en otra bolsa ---//

                    $existsProcessPool = CvPoolProcess::where("process_id", $process)->where("budget_id", $dataBudget)->first();

                    if (empty($existsProcessPool)) {

                        if ($poolProcess->save()) {

                            $status = 1;

                            // --- Guardar informacion del buscador --- //
                            $this->infoSearchPool($pool->id);
                        }
                    } else {
                        $status = 0;
                    }
                }
            }
        }

        if ($status == 1) {
            return [
                "message" => "Registro actualizado",
                "response_code" => 200,
            ];
        } else {
            return [
                "message" => "Los presupuestos del procedimiento ya se encuentran registrados en otra bolsa",
                "response_code" => 500,
            ];
        }
    }

    // *** Eliminar bolsa y liberar los presupuestos con procedimientos *** //
    public function destroy($id)
    {

        $pool = CvPool::find($id);

        if (empty($pool)) {
            return [
                "message" => "La bolsa no existe en el sistema",
                "response_code" => 500,
            ];
        }

        //--- Eliminar primero la informacion de la pivote de bolsa con presupuestos y procedimientos ---//

        $poolDeletePivot = CvPoolProcess::where("pool_id", $pool->id)->get();

        foreach ($poolDeletePivot as $poolPivot) {

            $poolPivot->delete();
        }

        //--- Eliminar bolsa ---//

        if ($pool->delete()) {
            return [
                "message" => "Registro eliminado con exito",
                "response_code" => 500,
            ];
        }
    }

    //*** Consultar los presupuestos por procedimiento ***//

    public function consultBudgetByProcess()
    {
        $process = CvProcess::get();

        if (empty($process)) {
            return [
                "message" => "No se encuentran procedimientos en el sistema",
                "response_code" => 500,
            ];
        }
        $budgetProcess = array();
        foreach ($process as $dataProcess) {
            $array_budget = array();
            $array_tasksopens = array();
            $insert = 0;
            $taskProcess = $dataProcess->processByTasks->where('task_sub_type_id', '>=', 4)->first();
            if (!empty($taskProcess)) {
                if (count($taskProcess->budget) > 0) {
                    foreach ($taskProcess->budget as $dataBudget) {
                        $role_name = null;
                        $role_id = null;
                        $byactivitie = CvActionByActivity::where('action_id', $dataBudget->actionsMaterials->action->id);
                        if ($byactivitie->exists()) {
                            $coordination = CvActivityCoordination::where('activity_id', $byactivitie->first()->activity_id);
                            if ($coordination->exists()) {
                                $role_name = $coordination->first()->roleadd->name;
                                $role_id = $coordination->first()->roleadd->id;
                            }
                        }
                        if ($role_id == $this->userLoggedInRol()) {
                            $insert = 1;
                            if (CvPoolProcess::where('budget_id', $dataBudget->id)->exists()) {
                                $detail_task_open['selected'] = true;
                            } else {
                                $detail_task_open['selected'] = false;
                                array_push($array_budget, array(
                                    "id" => $dataBudget->id,
                                    "value" => $dataBudget->value,
                                    "length" => $dataBudget->length,
                                    "hash_map" => $dataBudget->hash_map,
                                    "good_practicess" => $dataBudget->good_practicess,
                                    "task_id" => $dataBudget->task_id,
                                    "action_material_id" => $dataBudget->action_material_id,
                                    "action_id" => $dataBudget->actionsMaterials->id,
                                    "action" => $dataBudget->actionsMaterials->action->name,
                                    "role_name" => $role_name,
                                    "role_id" => $role_id,
                                    "selected" => $detail_task_open['selected'],
                                    "type" => $dataBudget->actionsMaterials->action->type,
                                    "material" => $dataBudget->actionsMaterials->budgetPriceMaterial->name,
                                ));
                            }
                        }
                    }
                }
            }
            if (CvTaskOpen::where('process_id', $dataProcess->id)->exists()) {
                //---Data tarea abierta---//
                $all_task_open = CvTaskOpen::where('process_id', $dataProcess->id)->get();
                foreach ($all_task_open as $detail_task_open) {
                    if (CvPoolProcess::where('task_open_id', $detail_task_open->id)->exists()) {
                        $detail_task_open['selected'] = true;
                    } else {
                        $detail_task_open['selected'] = false;
                        $detail_task_open = $this->typeAndRolTaskOpen($detail_task_open);
                        if ($detail_task_open->type != "contratista") {
                            if ($detail_task_open->role_id == null || $detail_task_open->role_id == $this->userLoggedInRol()) {
                                //cargar solamente las tareas que tienen valor
                                if (CvTaskOpenBudget::where('task_open_id', $detail_task_open->id)->exists()) {
                                    $insert = 1;
                                    array_push($array_tasksopens, $detail_task_open);
                                }
                            }
                        }
                    }
                }
            }
            if ($insert == 1) {
                array_push($budgetProcess, array(
                    "id" => $dataProcess->id,
                    "name" => $dataProcess->name,
                    "budget" => $array_budget,
                    "task_opens" => $array_tasksopens,
                ));
            }
        }
        return array_values($budgetProcess);
    }

    //*** Consultar los presupuestos asignados a la bolsa ***//

    public function consultBudgetByPool($id_pool)
    {
        $process = CvProcess::get();
        if (empty($process)) {

            return [
                "message" => "No se encuentran procedimientos en el sistema",
                "response_code" => 500,
            ];
        }
        $budgetProcess = array();
        foreach ($process as $dataProcess) {
            if (CvPoolProcess::where('pool_id', $id_pool)->where('process_id', $dataProcess->id)->exists()) {
                $array_budget = array();
                $array_tasksopens = array();
                $insert = 0;
                $taskProcess = $dataProcess->processByTasks->where('task_sub_type_id', '>=', 4)->first();
                if (!empty($taskProcess)) {
                    if (count($taskProcess->budget) > 0) {
                        foreach ($taskProcess->budget as $dataBudget) {

                            if (CvPoolProcess::where('pool_id', $id_pool)->where('budget_id', $dataBudget->id)->exists()) {
                                $role_name = null;
                                $role_id = null;
                                $byactivitie = CvActionByActivity::where('action_id', $dataBudget->actionsMaterials->action->id);
                                if ($byactivitie->exists()) {
                                    $coordination = CvActivityCoordination::where('activity_id', $byactivitie->first()->activity_id);
                                    if ($coordination->exists()) {
                                        $role_name = $coordination->first()->roleadd->name;
                                        $role_id = $coordination->first()->roleadd->id;
                                    }
                                }
                                if ($role_id == null || $role_id == $this->userLoggedInRol()) {
                                    $insert = 1;
                                    array_push($array_budget, array(
                                        "id" => $dataBudget->id,
                                        "value" => $dataBudget->value,
                                        "length" => $dataBudget->length,
                                        "hash_map" => $dataBudget->hash_map,
                                        "good_practicess" => $dataBudget->good_practicess,
                                        "task_id" => $dataBudget->task_id,
                                        "action_material_id" => $dataBudget->action_material_id,
                                        "action_id" => $dataBudget->actionsMaterials->id,
                                        "action" => $dataBudget->actionsMaterials->action->name,
                                        "role_name" => $role_name,
                                        "role_id" => $role_id,
                                        "selected" => true,
                                        "type" => $dataBudget->actionsMaterials->action->type,
                                        "material" => $dataBudget->actionsMaterials->budgetPriceMaterial->name,
                                    ));
                                }
                            }
                        }
                    }
                }
                if (CvTaskOpen::where('process_id', $dataProcess->id)->exists()) {
                    $all_task_open = $dataProcess->taskOpenProcess;
                    foreach ($all_task_open as $detail_task_open) {
                        if (CvPoolProcess::where('pool_id', $id_pool)->where('task_open_id', $detail_task_open->id)->exists()) {
                            $detail_task_open['selected'] = true;
                            $detail_task_open = $this->typeAndRolTaskOpen($detail_task_open);
                            if ($detail_task_open->type != "contratista") {
                                if ($detail_task_open->role_id == null || $detail_task_open->role_id == $this->userLoggedInRol()) {
                                    $insert = 1;
                                    array_push($array_tasksopens, $detail_task_open);
                                }
                            }
                        }
                    }
                }
                if ($insert == 1) {
                    array_push($budgetProcess, array(
                        "id" => $dataProcess->id,
                        "name" => $dataProcess->name,
                        "budget" => $array_budget,
                        "task_opens" => $array_tasksopens,
                    ));
                }
            }
        }
        return array_values($budgetProcess);
    }

    //*** Consulta general o especifica de la bolsa ***//

    public function consultGeneralSpecificPool($pool)
    {

        $poolByProcess["contractor"] = null;
        $pool["contractor"] = null;

        foreach ($pool->poolByProcess as $index => $poolByProcess) {

            //--- Consultar si cada presupuesto con acciones tiene un contratista para la bolsa ---//
            $poolActionByUserData = CvPoolActionByUser::where("pool_by_process_id", $poolByProcess->id)->first();
            if ($poolByProcess->budget_id != null) {
                $budget = CvBudget::find($poolByProcess->budget_id);

                // --- Acciones por el material --- //
                $materialAction = CvBudgetActionMaterial::find($budget->action_material_id);
                $action = CvActions::find($materialAction->action_id);
                $material = CvBudgetPriceMaterial::find($materialAction->budget_prices_material_id);

                if (isset($action) && isset($material)) {
                    $poolByProcess["action"] = $action->name;
                    $poolByProcess["task_open"] = false;
                    $poolByProcess["action_id"] = $action->id;
                    $poolByProcess["type"] = $action->type;
                    $poolByProcess["material"] = $material->name;
                }

                $othercamps = CvOtherInfoContractor::where('pool_id', $pool->id);
                if ($othercamps->exists()) {
                    $user = User::find($othercamps->first()->user_id);
                    $pool["contractor"] = array(
                        "id" => $user->id,
                        "name" => $user->name,
                    );
                }
            } else {

                $taskopen = CvTaskOpen::find($poolByProcess->task_open_id);

                if (CvPoolProcess::where('task_open_id', $taskopen->id)->exists()) {

                    $poolByProcess["task_open"] = true;
                    $poolByProcess["task_open_id"] = $taskopen->id;
                    $poolByProcess["task_open_description"] = $taskopen->description;

                    switch ($taskopen->task_open_sub_type_id) {
                        case 1:
                            $poolByProcess['task_open_type'] = "abierta";
                            break;
                        case 2:
                            $poolByProcess['type'] = "psa";
                            break;
                        case 3:
                        case 26:
                        case 27:
                        case 28:
                        case 29:
                        case 30:
                            $poolByProcess['task_open_type'] = "erosivo";
                            break;
                        case 4:
                        case 21:
                        case 22:
                        case 23:
                        case 24:
                        case 25:
                            $poolByProcess['task_open_type'] = "hidrico";
                            break;
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                            $poolByProcess['task_open_type'] = "comunicacion";
                            break;
                        case 18:
                        case 19:
                        case 20:
                            $poolByProcess['task_open_type'] = "contratista";
                            break;
                        case 31:
                        case 32:
                        case 33:
                        case 34:
                        case 35:
                        case 36:
                        case 37:
                        case 38:
                            $poolByProcess['task_open_type'] = "especial";
                            break;
                        default:
                            $poolByProcess['task_open_type'] = "na";
                            break;
                    }
                } else {
                    unset($poolByProcess['id']);
                    unset($poolByProcess['pool_id']);
                    unset($poolByProcess['process_id']);
                    unset($poolByProcess['budget_id']);
                    unset($poolByProcess['task_open_id']);
                }
            }
        }

        return $pool;
    }

    public function insert_activities(Request $request)
    {

        $ContractorController = new GeneralContractorsController();
        $budget_second = $ContractorController->budgetContractor($request); //

        if ($budget_second == 1) {
            return [
                "message" => "Acciones ingresadas",
                "code" => 200,
            ];
        } else {
            return $budget_second;
        }
    }

    public function update_activities(Request $request)
    {

        $ContractorController = new GeneralContractorsController();
        $budget_second = $ContractorController->budgetContractor($request); //

        if ($budget_second == 1) {
            return [
                "message" => "Acciones Actualizadas",
                "code" => 500,
            ];
        } else {
            return $budget_second;
        }
    }

    //--- agrega nuevas acciones a la bolsa ---//

    public function createSelectActionsPoolByContractor(Request $request)
    {

        $pool = $request->pool_id;
        $this->addTaskOpenInPool($request->task_proces, $pool);

        $countRegisters = array();

        //--- Registrar nuevas acciones a la bolsa relacionado con contratista ---//
        foreach ($request->process as $key => $budget) {
            foreach ($budget as $onebudget) {
                $poolByProcess = CvPoolProcess::where("pool_id", $pool)->where('process_id', $key)->where('budget_id', $onebudget);
                if ($poolByProcess->exists()) {
                    $new_pool_process = $poolByProcess->first();
                } else {
                    $new_pool_process = new CvPoolProcess();
                }
                $new_pool_process->pool_id = $pool;
                $new_pool_process->process_id = $key;
                $new_pool_process->budget_id = $onebudget;
                $new_pool_process->save();

                array_push($countRegisters, 1);
                // $ContractorController = new GeneralContractorsController();
                // $ContractorController->budgetContractor($budget["id_budget"],$budget["value"],$budget["id_user"],$origin_resource->first()->id);
                // --- Guardar informacion del buscador --- //
                $this->infoSearchPool($pool);
            }
        }

        //--- Registrar nuevas acciones a la bolsa relacionado con contratista ---//
        foreach ($request->task_proces as $key => $budget) {
            foreach ($budget as $onebudget) {
                $poolByProcess = CvPoolProcess::where("pool_id", $pool)->where('process_id', $key)->where('task_open_id', $onebudget);
                if ($poolByProcess->exists()) {
                    $new_pool_process = $poolByProcess->first();
                } else {
                    $new_pool_process = new CvPoolProcess();
                }
                $new_pool_process->pool_id = $pool;
                $new_pool_process->process_id = $key;
                $new_pool_process->task_open_id = $onebudget;
                $new_pool_process->save();

                array_push($countRegisters, 1);
                // $ContractorController = new GeneralContractorsController();
                // $ContractorController->budgetContractor($budget["id_budget"],$budget["value"],$budget["id_user"],$origin_resource->first()->id);
                // --- Guardar informacion del buscador --- //
                $this->infoSearchPool($pool);
            }
        }

        $this->asigmentContractorByActionsThePool($pool);
        //--- Validar que se registraron las acciones de los procedimientos a cada contratista ---//

        if (count($countRegisters) > 0) {
            return [
                "message" => "Registro exitoso",
                "response_code" => 200,
            ];
        } else {

            return [
                "message" => "Los presupuestos ya fueron seleccionados a un contratista",
                "response_code" => 500,
            ];
        }
    }

    //--- Actualizar la seleccion de acciones por bolsa para contratista ---//

    public function updateSelectActionsPoolByContractor(Request $request, $id)
    {

        $poolActionByUserCount = 0;
        $state = 0;

        //--- Consultar los id de los registros de las bolsas que han sido registrados en el sistema ---//

        $poolProcess = CvPoolProcess::select('id')->get();

        //--- Consultar las acciones que tiene el contratista ---//

        foreach ($poolProcess as $poolProcessId) {

            //--- Eliminar las acciones del usuario contratista para ser sobreescritas ---//

            $poolActionByProcess = CvPoolActionByUser::where("pool_by_process_id", $poolProcessId->id)->first();

            if (!empty($poolActionByProcess)) {
                $poolActionByProcess->delete();
            }
        }

        foreach ($request->pool as $poolData) {
            foreach ($poolData as $pool) {

                $poolActionByUser = CvPoolActionByUser::where("pool_by_process_id", $pool)->where("user_id", "<>", $request->contractor)->exists();

                if (!$poolActionByUser) {

                    $poolContractor = new CvPoolActionByUser();
                    $poolContractor->pool_by_process_id = $pool;
                    $poolContractor->user_id = $request->contractor;

                    if ($poolContractor->save()) {
                        $state = 1;
                    }
                } else {
                    $poolActionByUserCount++;
                }
            }
        }

        if ($poolActionByUserCount > 0) {

            return [
                "message" => "Los presupuestos ya fueron seleccionados a un contratista",
                "response_code" => 500,
            ];
        }

        if ($state == 1) {

            return [
                "message" => "Registro actualizado",
                "response_code" => 200,
            ];
        }
    }

    //*** Consecutivo de la bolsa ***//

    public function consecutivePool()
    {

        //--- Consultar bolsa todas la bolsas para saber si se cuenta con el consecutivo ---//

        $poolConsecutive = CvPool::get();

        if (count($poolConsecutive) > 0) {

            foreach ($poolConsecutive as $pool) {

                if ($pool->consecutive == $this->consecutive()) {
                    return $this->consecutive();
                }
            }
        }

        return $this->consecutive();
    }

    //*** Generar consecutivo ***//

    public function consecutive()
    {

        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    }

    //*** Filtrar informacion del usuario para el buscador ***//

    public function infoSearchPool($pool_id)
    {

        $pool = CvPool::find($pool_id);

        if (empty($pool)) {
            return [
                "message" => "La bolsa no existe en el sistema",
                "response_code" => 500,
            ];
        }

        //--- Consultar si la bolsa los predios que estan registrados a partir de sus procedimientos ---//

        $infoProperties = array();
        $infoContractors = array();

        if (!empty($pool->poolByProcess)) {

            foreach ($pool->poolByProcess as $poolByProcess) {

                //--- Obtener la tarea de encuesta del procedimiento para obtener su información ---//

                $process = CvProcess::find($poolByProcess->process_id);

                foreach ($process->processByTasks as $task) {

                    if ($task->task_type_id == 3) {

                        if (!empty($task->property_id)) {

                            $property = CvProperty::find($task->property_id);

                            if ($property->info_json_general != null) {

                                $dataProperty = json_decode($property->info_json_general, true);
                                array_push($infoProperties, $dataProperty["property_name"]);
                            }
                        }
                    }
                }

                //--- Obtener los contratistas que estan vinculados a las acciones de la bolsa ---//
                $poolProcess = CvPoolProcess::find($poolByProcess->id);

                if (!empty($poolProcess)) {

                    if (!empty($poolProcess->poolActionsByUserContractor->user_id)) {

                        $user = User::find($poolProcess->poolActionsByUserContractor->user_id);

                        if ($user->role_id == 5) {

                            if ($user->contractorOne != null) {
                                $typePerson = ($user->contractorOne->type_person == 1) ? $user->contractorOne->type_person = "Natural" : $user->contractorOne->type_person = "Jurídico";
                            } else {
                                $typePerson = "Contratista";
                            }

                            array_push($infoContractors, array(
                                // "Nit" => $nit,
                                 "Tipo de persona" => $typePerson,
                                "Correo electrónico" => $user->email,
                                "Usuario" => $user->name,
                            ));
                        }
                    }
                }
            }
        }

        //--- Obtener el nombre de las acciones que se encuentran en la bolsa ---//

        $arrayActions = array();

        if (!empty($pool->poolByBudgetPivot)) {

            foreach ($pool->poolByBudgetPivot as $budget) {

                array_push($arrayActions, CvBudgetActionMaterial::find($budget->action_material_id)->actionOne->name);
            }
        }

        //--- Mostrar la información del predio o contratistas si estan vacios o llenos ---//

        $order = array("[{", "}]", "\r\n", "\n", "\r", "\"", "\\", "{", "}");

        $dataProperties = (count($infoProperties) > 0) ? array_unique($infoProperties) : "Bolsa no cuenta con predios";
        $dataContractos = (count($infoContractors) > 0) ? str_replace($order, " ", json_encode($infoContractors, JSON_UNESCAPED_UNICODE)) : "No hay contratistas para esta bolsa";

        //--- Información general de la bolsa ---//
        $name = $pool->name . " - " . $pool->consecutive;
        $properties = (is_array($dataProperties)) ? implode(", ", $dataProperties) : $dataProperties;
        $actionsBudget = (is_array($arrayActions)) ? implode(", ", $arrayActions) : $arrayActions;
        $type = "bolsa";

        $description = "Predios" . ": " . $properties . ", " .
            "Acciones" . ": " . $actionsBudget . ", " .
            "Contratistas" . ": " . $dataContractos;

        $dataSearch = [
            "name" => $name,
            "description" => $description,
            "type" => $type,
            "entity_id" => $pool->id,
        ];

        //--- Instancia del modelo del buscador universal con algolia ---//

        $searchAlgoliaController = new SearchAlgoliaController();

        if ($searchAlgoliaController->registerSearchUniversal($dataSearch) == 200) {
            return true;
        }
    }

    public function saveContract(Request $request)
    {
        $campos = $request->all();
        $dataFile = $this->saveFile($request->file);

        $cvContract = new CvContract();
        $cvContract->name = $dataFile['name'];
        $cvContract->url = $dataFile['url'];
        $cvContract->extension = $dataFile['extension'];
        $cvContract->pool_id = $campos['pool_id'];
        $cvContract->type_contract_bolsa_id = $campos['type_contract'];
        $cvContract->type_file_contract = $campos['type_file'];

        if ($cvContract->save()) {
            return [
                'message' => 'Archivo guardado.',
                'code' => 200,
            ];
        }
        return [
            'message' => 'Ha ocurrido un error',
            'code' => 500,
        ];
    }

    public function getContractByPoolId($idPool)
    {
        $ModelPool = CvPool::findOrFail($idPool);
        foreach ($ModelPool->contract as $contract) {
            $contract->typeContract;
            $contract->typeFile;
        }
        return $ModelPool->contract;
    }

    public function downloadFile($idFile)
    {
        $modelContract = CvContract::findOrFail($idFile);
        return Storage::disk('storage')->get($modelContract->url . $modelContract->name);
    }

    public function validatePoolByProcess($id_proces)
    {
        if (CvPoolProcess::where('process_id', $id_proces)->exists()) {
            return ['verified' => 'true'];
        } else {
            return ['verified' => 'false'];
        }
    }

    public function validateExecutionByProcess($id_proces)
    {
        $pool_all = CvPoolProcess::where('process_id', $id_proces)->get();
        foreach ($pool_all as $pool) {
            if (CvBudgetByBudgetExcution::where('budget_contractor_id', $pool->Budget->budgetContractor->id)->exists()) {
                return ['verified' => 'true'];
            }
        }
        return ['verified' => 'false'];
    }

    public function validateExecutionByPool($id_pool)
    {
        $pool = CvPool::find($id_pool);
        foreach ($pool->poolByBudgetPivot as $budget) {

            if (CvBudgetByBudgetExcution::where('budget_contractor_id', $budget->budgetContractor->id)->exists()) {
                return ['verified' => 'true'];
            }
        }

        return ['verified' => 'false'];
    }

    private function saveFile($fileContract)
    {
        $file = $fileContract;
        $ffile = $fileContract;
        $ffile = str_replace('/tmp/', '', $ffile);
        $filename = sha1(time() . $ffile);
        $extension = $file->getClientOriginalExtension();

        $nameFile = $filename . '_' . $file->getClientOriginalName() . '.' . $extension;

        Storage::disk('storage')->put('contractPool/' . $nameFile, File::get($file));

        return [
            'name' => $nameFile,
            'url' => '/contractPool/',
            'extension' => $extension,
        ];
    }

    public function consultActiionsByallProcess($id_process)
    {

        //--- consultar las tareas que contiene un procedimiento ---//

        $process = CvProcess::get();

        if (empty($process)) {

            return [
                "message" => "No se encuentran procedimientos en el sistema",
                "response_code" => 500,
            ];
        }

        //--- Obtener los presupuestos ---//
        $budgetProcess = array();

        foreach ($process as $dataProcess) {

            foreach ($dataProcess->processByTasks as $taskProcess) {

                //--- Filtrar por lo que tengan solo presupuesto ---//

                if (count($taskProcess->budget) > 0) {

                    //--- Validar que los presupuestos y los procesos no se encuentren en otra bolsa ---//

                    $arrayBudgetInfo = array();

                    foreach ($taskProcess->budget as $item => $budgetInfo) {

                        $poolProcessExist = CvPoolProcess::where('pool_id', '!=', $id_process)->where("process_id", $dataProcess->id)->where("budget_id", $budgetInfo->id)->count();

                        if ($poolProcessExist != 0) {

                            unset($taskProcess->budget[$item]);
                        } else {
                            array_push($arrayBudgetInfo, $budgetInfo);
                        }
                    }

                    //--- Quitar los indices de cada array del objeto de presupuesto ---//

                    $taskProcess->budget = $arrayBudgetInfo;

                    //--- Guardar presupuestos de los procedimientos ---//

                    array_push($budgetProcess, array(
                        "id" => $taskProcess->pivot->process_id,
                        "name" => $dataProcess->name,
                        "budget" => $taskProcess->budget,
                    ));
                }
            }
        }

        //--- Finalizacion de consulta personalizada con las acciones y materiales de cada presupuesto ---//

        foreach ($budgetProcess as $infoBudget) {
            foreach ($infoBudget["budget"] as $budgetData) {

                $materialAction = CvBudgetActionMaterial::find($budgetData->action_material_id);
                $action = CvActions::find($materialAction->action_id);
                $material = CvBudgetPriceMaterial::find($materialAction->budget_prices_material_id);

                $budgetData["action"] = $action->name;
                $budgetData["action_id"] = $action->id;
                $budgetData["type"] = $action->type;
                $budgetData["material"] = $material->name;
                $budgetData["material_id"] = $material->id;
            }
        }

        return array_values($budgetProcess);
    }

//Esta es la funcion antigua de allactivities
    public function LastFunctiontheall_activities($pool_id)
    {
        $action = array();
        $BudgetProcess = $this->consultActiionsByallProcess($pool_id);
        foreach ($BudgetProcess as $proces) {
            foreach ($proces['budget'] as $budget) {
                if (CvPoolProcess::where('pool_id', $pool_id)->where('budget_id', $budget->id)->exists()) {
                    $value_action = 0;
                    $validate_actions_budget = CvBudgetByBudgetContractor::where('budget_id', $budget->id);
                    if ($validate_actions_budget->exists()) {
                        $value_action = $validate_actions_budget->first()->tariffAction->budget_contractor;
                    }
                    if (CvDetailOriginResource::where('budget_id', $budget->id)->exists()) {
                        $budget_array = array();
                        $contribution_array = array();
                        foreach ($BudgetProcess as $sherproces) {
                            foreach ($sherproces['budget'] as $sherbudget) {
                                $origin = CvDetailOriginResource::where('budget_id', $sherbudget['id']);
                                if ($origin->exists()) {
                                    if ($budget['action_id'] == $sherbudget['action_id']) {
                                        array_push($budget_array, array(
                                            $sherbudget['id'],
                                        ));

                                        $contributions_all = $origin->get();
                                        foreach ($contributions_all as $contributions_one) {
                                            $value_contribution = 0;
                                            $validate_contribution_budget = CvBudgetByBudgetContractor::where('budget_id', $contributions_one->budget_id);
                                            if ($validate_contribution_budget->exists()) {

                                                $validate_value_contribution = CvContractorBudgetDetailOrigin::where('budget_contractor_id', $validate_contribution_budget->first()->id)->where('contribution_id', $contributions_one->contribution_id);
                                                if ($validate_value_contribution->exists()) {
                                                    $value_contribution = $validate_value_contribution->first()->ultimate_committed;
                                                }
                                            }
                                            array_push($contribution_array, array(
                                                "associated_name" => $contributions_one->associatedContribution->associated->name,
                                                "associated_id" => $contributions_one->associated_id,
                                                "contribution_id" => $contributions_one->contribution_id,
                                                "budget_id" => $contributions_one->budget_id,
                                                "origin_id" => $contributions_one->origin_id,
                                                "contribution_value" => $value_contribution,
                                            ));
                                        }
                                    }
                                }
                            }
                        }

                        if (count($action) == 0) {
                            array_push($action, array(
                                "action_name" => $budget['action'],
                                "material_name" => $budget['material'],
                                "action_id" => $budget['action_id'],
                                "material_id" => $budget['material_id'],
                                "action_value" => $value_action,
                                "budget" => $budget_array,
                                "contributions" => $contribution_array,
                            ));
                        } else {
                            $validate = 0;
                            foreach ($action as $detail) {
                                if ($detail['action_id'] == $budget['action_id']) {
                                    $validate = 1;
                                }
                            }
                            if ($validate == 0) {
                                array_push($action, array(
                                    "action_name" => $budget['action'],
                                    "material_name" => $budget['material'],
                                    "action_id" => $budget['action_id'],
                                    "material_id" => $budget['material_id'],
                                    "action_value" => $value_action,
                                    "budget" => $budget_array,
                                    "contributions" => $contribution_array,
                                ));
                            }
                        }
                    }
                }
            }
        }
        return $action;
    }

    public function all_activities($pool_id)
    {
        $info = array();
        $action = array();
        $task_opens = array();
        //Trae las acciones agregadas a la bolsa
        $poolByProcess = CvPoolProcess::where('pool_id', $pool_id)->where('budget_id', '!=', null)->get();
        foreach ($poolByProcess as $poolProcess) {
            $ArrayContri = array();
            //Consulta si esa acciones ya tiene origen de recursos creados agregados en el presupuesto
            $validateOrigin = CvOriginResource::where('budget_id', $poolProcess->budget_id);
            if ($validateOrigin->exists()) {
                $contributionsOrigin = $validateOrigin->first()->detailOriginResource;
                //Crea array con los origines dados en el presupuesto
                foreach ($contributionsOrigin as $contri) {
                    array_push($ArrayContri, array(
                        "associated_name" => $contri->associatedContribution->thisisassociate->name,
                        "associated_id" => $contri->associated_id,
                        "contribution_id" => $contri->contribution_id,
                        "budget_id" => $contri->budget_id,
                        "origin_id" => $contri->origin_id,
                        "contribution_value" => $contri->ultimate_committed,
                    ));
                }
                //La accion tiene un costo del tarifador del contratista
                $value_action = 0;
                $validate_actions_budget = CvBudgetByBudgetContractor::where('budget_id', $poolProcess->budget_id);
                if ($validate_actions_budget->exists()) {
                    $value_action = $validate_actions_budget->first()->price_contractor;
                }
                $budget = $poolProcess->Budget->actionsMaterials;
                array_push($action, array(
                    "action_name" => $budget->actionOne->name,
                    "material_name" => $budget->budgetPriceMaterial->name,
                    "action_id" => $budget->actionOne->id,
                    "material_id" => $budget->budgetPriceMaterial->id,
                    "action_value" => $value_action,
                    "type" => 'budget',
                    "budget_id" => $poolProcess->budget_id,
                    "contributions" => $ArrayContri,
                ));
            }
        }

        //Trae las tareas abiertas agregadas a la bolsa
        $poolByProcesstask = CvPoolProcess::where('pool_id', $pool_id)->where('task_open_id', '!=', null)->get();
        foreach ($poolByProcesstask as $poolProcesstask) {
            $ArrayContri = array();
            $value_task = 0;
            //Consulta si esa acciones ya tiene origen de recursos creados agregados en el presupuesto
            $validateTaskBudegt = CvTaskOpenBudget::where('task_open_id', $poolProcesstask->task_open_id);
            if ($validateTaskBudegt->exists()) {
                $contributionsOrigin = $validateTaskBudegt->get();
                //Crea array con los origines dados en el presupuesto
                foreach ($contributionsOrigin as $contri) {
                    $value_task = $value_task + $contri->amount;
                    array_push($ArrayContri, array(
                        "associated_name" => $contri->associateContribution->thisisassociate->name,
                        "associated_id" => $contri->associateContribution->associated_id,
                        "contribution_id" => $contri->associated_contributions_id,
                        "task_open_id" => $contri->task_open_id,
                        "task_budget_id" => $contri->id,
                        "contribution_value" => $contri->amount,
                    ));
                }
                //La accion tiene un costo del tarifador del contratista
                $datatask_open = CvTaskOpen::find($poolProcesstask->task_open_id);

                array_push($action, array(
                    "task_description" => $datatask_open->description,
                    "task_id" => $datatask_open->id,
                    "type_process" => $datatask_open->process->type_process,
                    "action_value" => $value_task,
                    "type" => 'task_open',
                    "contributions" => $ArrayContri,
                ));
            }
        }

        return $action;
    }

    public function detail_activities($pool_id)
    {

        $action = array();
        //Trae las acciones agregadas a la bolsa
        $poolByProcess = CvPoolProcess::where('pool_id', $pool_id)->where('budget_id', '!=', null)->get();
        foreach ($poolByProcess as $poolProcess) {
            $ArrayBudget = array();
            if (true) {

                //La accion tiene un costo del tarifador del contratista
                $value_action = 0;
                $asignen_cost = false;
                $validate_actions_budget = CvBudgetByBudgetContractor::where('budget_id', $poolProcess->budget_id);
                if ($validate_actions_budget->exists()) {
                    $value_action = $validate_actions_budget->first()->tariffAction->budget_contractor;
                    if ($value_action != 0) {
                        $asignen_cost = true;
                    }
                }

                $budget = $poolProcess->Budget->actionsMaterials;
                if (count($action) == 0) {
                    array_push($ArrayBudget, $poolProcess->budget_id);
                    array_push($action, array(
                        "action_name" => $budget->actionOne->name,
                        "material_name" => $budget->budgetPriceMaterial->name,
                        "action_id" => $budget->actionOne->id,
                        "material_id" => $budget->budgetPriceMaterial->id,
                        "action_value" => $value_action,
                        "validate_coste" => $asignen_cost,
                        "pool_id" => $pool_id,
                        "budget" => $ArrayBudget,
                    ));
                } else {
                    $validateInser = 0;
                    for ($i = 0; $i < count($action); $i++) {
                        if ($action[$i]['action_id'] == $budget->actionOne->id) {
                            $validateInser = 1;
                            array_push($action[$i]['budget'], $poolProcess->budget_id);
                        }
                    }
                    if ($validateInser == 0) {
                        array_push($ArrayBudget, $poolProcess->budget_id);
                        array_push($action, array(
                            "action_name" => $budget->actionOne->name,
                            "material_name" => $budget->budgetPriceMaterial->name,
                            "action_id" => $budget->actionOne->id,
                            "material_id" => $budget->budgetPriceMaterial->id,
                            "action_value" => $value_action,
                            "validate_coste" => $asignen_cost,
                            "pool_id" => $pool_id,
                            "budget" => $ArrayBudget,
                        ));
                    }
                }
            }
        }
        return $action;
    }

    public function deleteFile($id)
    {
        $fileContractor = CvContract::findOrFail($id);
        if ($fileContractor->delete()) {
            return [
                'message' => 'Contrato eliminado.',
                'code' => 200,
            ];
        } else {
            return [
                'message' => 'Contrato no eliminado.',
                'code' => 500,
            ];
        }
    }

    //*** Consulta el presupuesto por bolsa del primero presupuesto ***//
    public function budgetReportPool($id_pool)
    {

        $action = array();
        $potential = array();
        $info = array();
        $total = 0;
        $pool_process = CvPoolProcess::where('pool_id', $id_pool);

        /**
         * Inicializacion variables de porcentajes dinamicas a partir del budget
         */
        $administration = 0;
        $iva = 0;
        $utility = 0;

        if (!$pool_process->exists()) {
            return [
                'message' => 'No se an agregado acciones a la bolsa.',
                'code' => 500,
            ];
        }

        $pool_by_process = CvPool::find($id_pool)->poolByProcess;

        foreach ($pool_by_process as $proces) {
            $detail_process = $proces->Process;

            $budget = $proces->Budget;

            /**
             * Almacenar variables de porcentajes dinamicos
             */
            $administration = $proces->Budget->administration;
            $utility = $proces->Budget->utility;
            $iva = $proces->Budget->iva;

            if ($proces->budget_id != null) {
                if (CvBudgetByBudgetContractor::where('budget_id', $budget->id)->exists()) {
                    $tariff = $budget->budgetContractor->tariffAction;
                    $total = $total + $budget->budgetContractor->price_contractor;
                    array_push($action, array(
                        "action_name" => $tariff->actionTable->name,
                        "material_unit" => $tariff->materialTable->units->name,
                        "budget_quantity" => $budget->length,
                        "budget_id" => $budget->id,
                        "task_id" => null,
                        "budget_value" => $budget->budgetContractor->budget_contractor,
                        "budget_value_total" => $budget->budgetContractor->price_contractor,
                    ));
                }
            }

            if ($proces->task_open_id != null) {
                $task_open = CvTaskOpen::where('id', $proces->task_open_id);
                if ($task_open->exists()) {
                    $tariff = $task_open->first();
                    if (!empty($tariff->taskOpenBudget)) {
                        $total = $total + $tariff->taskOpenBudget->amount;
                        array_push($action, array(
                            "action_name" => $tariff->description,
                            "material_unit" => 'Tarea',
                            "budget_quantity" => 1,
                            "task_id" => $tariff->id,
                            "budget_id" => null,
                            "budget_value" => $tariff->taskOpenBudget->amount,
                            "budget_value_total" => $tariff->taskOpenBudget->amount,
                        ));
                    }
                }
            }
        }

        $info['data'] = $action;
        $info['budget_total'] = $total;
        $info['budget_adminstration'] = ($total * $administration) / 100;
        $info['budget_utility'] = ($total * $utility) / 100;
        $info['budget_iva'] = ($info['budget_utility'] * $iva) / 100;
        $info['technical_assistance'] = 0;
        $info['value_total'] = $info['budget_total'] + $info['budget_adminstration'] + $info['budget_utility'] + $info['budget_iva'] + $info['technical_assistance'];

        foreach ($pool_by_process as $proces) {
            if ($proces->budget_id != null) {
                $detail_process = $proces->Process;
                $poll = $detail_process->processByTasks->where('task_sub_type_id', '>=', 4)->first(); //TAREA
                $info_general = json_decode($poll->property->info_json_general, true);

                $validate = $proces->Budget->budgetContractor;
                if (empty($validate)) {
                    $value = 0;
                } else {
                    $value = $validate->price_contractor;
                }

                $admin = ($value * $administration) / 100;
                $utility = ($value * $utility) / 100;
                $iva_utility = ($utility * $iva) / 100;
                $subttal = $value + $admin + $utility + $iva_utility;

                if (empty($detail_process->potentialProperty)) {
                    $property_name = "";
                    $property_id = 0;
                } else {
                    $property_name = $detail_process->potentialProperty->property_name;
                    $property_id = $detail_process->potentialProperty->id;
                }
                if ($info['value_total'] == 0) {
                    $toinfo = $subttal;
                } else {
                    $toinfo = ($subttal * 100) / $info['value_total'];
                }
                $porcent = $toinfo;

                if ($property_id != 0) {
                    $validator = 0;
                    if (count($potential) > 0) {
                        for ($i = 0; $i < count($potential); $i++) {
                            if ($property_id == $potential[$i]['potential_id']) {
                                $validator = 1;
                                $potential[$i]['budget_total'] = $potential[$i]['budget_total'] + $value;

                                $admin = ($potential[$i]['budget_total'] * $administration) / 100;
                                $utility = ($potential[$i]['budget_total'] * $utility) / 100;
                                $iva_utility = ($utility * $iva) / 100;
                                $subttal = $potential[$i]['budget_total'] + $admin + $utility + $iva_utility;

                                if ($info['value_total'] == 0) {
                                    $toinfo = $subttal;
                                } else {
                                    $toinfo = ($subttal * 100) / $info['value_total'];
                                }
                                $porcent = $toinfo;

                                $potential[$i]['porcent'] = $porcent;
                                $potential[$i]['budget_adminstration'] = $admin;
                                $potential[$i]['budget_utility'] = $utility;
                                $potential[$i]['budget_iva'] = $iva_utility;
                                $potential[$i]['value_total'] = $subttal;
                            }
                        }
                    }
                    //Si no hay iguales
                    if ($validator == 0) {
                        array_push($potential, array(
                            "micro_basin" => $info_general['micro_basin'],
                            "municipality" => $info_general['municipality'],
                            "potential" => $property_name,
                            "potential_id" => $property_id,
                            "budget_total" => $value,
                            "porcent" => $porcent,
                            "budget_adminstration" => $admin,
                            "budget_utility" => $utility,
                            "budget_iva" => $iva_utility,
                            "value_total" => $subttal,
                        ));
                    }
                }
            }
        }

        $info['potential'] = $potential;
        return $info;
    }

    //*** Consulta el presupuesto por bolsa del segundo presupuesto ***//
    public function budgetReportPoolContractor($id_pool)
    {

        $action = array();
        $potential = array();
        $info = array();
        $total = 0;
        $pool_process = CvPoolProcess::where('pool_id', $id_pool);
        if (!$pool_process->exists()) {
            return [
                'message' => 'No se an agregado acciones a la bolsa.',
                'code' => 500,
            ];
        }

        /**
         * Inicializacion variables de porcentajes dinamicas a partir del budget
         */
        $administration = 0;
        $iva = 0;
        $utility = 0;

        $pool_by_process = CvPool::find($id_pool)->poolByProcess;

        foreach ($pool_by_process as $proces) {
            $detail_process = $proces->Process;

            $budget = $proces->Budget;

            $administration = $budget->administration;
            $utility = $budget->utility;
            $iva = $budget->iva;

            if ($proces->budget_id != null) {
                $second_budget = $proces->Budget->budgetContractor;
                if (!empty($second_budget)) {
                    $execution_validate = CvBudgetByBudgetExcution::where('budget_contractor_id', $second_budget->id);
                    if ($execution_validate->exists()) {
                        $tariff = $budget->budgetContractor->tariffAction;
                        $total = $total + $budget->budgetContractor->budgetExecution->price_execution;
                        array_push($action, array(
                            "action_name" => $tariff->actionTable->name,
                            "material_unit" => $tariff->materialTable->units->name,
                            "budget_quantity" => $budget->budgetContractor->budgetExecution->shape_leng,
                            "budget_value" => $budget->budgetContractor->budget_contractor,
                            "budget_value_total" => $budget->budgetContractor->budgetExecution->price_execution,
                        ));
                    }
                }
                if ($proces->task_open_id != null) {
                    $task_open = CvTaskOpen::where('id', $proces->task_open_id);
                    if ($task_open->exists()) {
                        $tariff = $task_open->first();

                        if (!empty($tariff->taskOpenBudget)) {
                            $total = $total + $tariff->taskOpenBudget->amount;
                            array_push($action, array(
                                "action_name" => $tariff->description,
                                "material_unit" => 'Tarea',
                                "budget_quantity" => 1,
                                "task_id" => $tariff->id,
                                "budget_id" => null,
                                "budget_value" => $tariff->taskOpenBudget->amount,
                                "budget_value_total" => $tariff->taskOpenBudget->amount,
                            ));
                        }
                    }
                }
            }
        }
        //Agrega imprevisto
        $unforeseen = CvUnforeseenTariffContractor::where('pool_id', $id_pool);
        if ($unforeseen->exists()) {
            $total = $total + $unforeseen->first()->budget_contractor;
            array_push($action, array(
                "action_name" => $unforeseen->first()->description,
                "material_unit" => "",
                "budget_quantity" => "",
                "budget_value" => $unforeseen->first()->budget_contractor,
                "budget_value_total" => $unforeseen->first()->budget_contractor,
            ));
        }
        $info['data'] = $action;
        $info['budget_total'] = $total;
        $info['budget_adminstration'] = ($total * $administration) / 100;
        $info['budget_utility'] = ($total * $utility) / 100;
        $info['budget_iva'] = ($info['budget_utility'] * $iva) / 100;
        $info['technical_assistance'] = 0;
        $info['value_total'] = $info['budget_total'] + $info['budget_adminstration'] + $info['budget_utility'] + $info['budget_iva'] + $info['technical_assistance'];

        foreach ($pool_by_process as $proces) {
            if ($proces->budget_id != null) {
                $detail_process = $proces->Process;
                $poll = $detail_process->processByTasks->where('task_sub_type_id', '>=', 4)->first(); //TAREA
                $first_budget_all = $poll->budget; //primer presupuesto
                $info_general = json_decode($poll->property->info_json_general, true);

                $validate = $proces->Budget->budgetContractor;
                if (empty($validate->budgetExecution)) {
                    $value = 0;
                } else {
                    $value = $validate->budgetExecution->price_execution;
                }
                $admin = ($value * $administration) / 100;
                $utility = ($value * $utility) / 100;
                $iva_utility = ($utility * $iva) / 100;
                $subttal = $value + $admin + $utility + $iva_utility;
                if ($info['value_total'] == 0) {
                    $toinfo = $subttal;
                } else {
                    $toinfo = ($subttal * 100) / $info['value_total'];
                }

                $porcent = $toinfo;
                if (empty($detail_process->potentialProperty)) {
                    $property_name = "";
                    $property_id = 0;
                } else {
                    $property_name = $detail_process->potentialProperty->property_name;
                    $property_id = $detail_process->potentialProperty->id;
                }

                if ($property_id != 0) {
                    $validator = 0;
                    if (count($potential) > 0) {
                        for ($i = 0; $i < count($potential); $i++) {
                            if ($property_id == $potential[$i]['potential_id']) {
                                $validator = 1;
                                $potential[$i]['budget_total'] = $potential[$i]['budget_total'] + $value;

                                $admin = ($potential[$i]['budget_total'] * $administration) / 100;
                                $utility = ($potential[$i]['budget_total'] * $utility) / 100;
                                $iva_utility = ($utility * $iva) / 100;
                                $subttal = $potential[$i]['budget_total'] + $admin + $utility + $iva_utility;

                                if ($info['value_total'] == 0) {
                                    $toinfo = $subttal;
                                } else {
                                    $toinfo = ($subttal * 100) / $info['value_total'];
                                }
                                $porcent = $toinfo;

                                $potential[$i]['porcent'] = $porcent;
                                $potential[$i]['budget_adminstration'] = $admin;
                                $potential[$i]['budget_utility'] = $utility;
                                $potential[$i]['budget_iva'] = $iva_utility;
                                $potential[$i]['value_total'] = $subttal;
                            }
                        }
                    }
                    //Si no hay iguales
                    if ($validator == 0) {
                        array_push($potential, array(
                            "micro_basin" => $info_general['micro_basin'],
                            "municipality" => $info_general['municipality'],
                            "potential" => $property_name,
                            "potential_id" => $property_id,
                            "budget_total" => $value,
                            "porcent" => $porcent,
                            "budget_adminstration" => $admin,
                            "budget_utility" => $utility,
                            "budget_iva" => $iva_utility,
                            "value_total" => $subttal,
                        ));
                    }
                }
            }
        }
        $info['potential'] = $potential;
        return $info;
    }

    public function generalExelReportForPool($type, $id_pool)
    {
        if ($type == 1) {
            $data = $this->budgetReportPool($id_pool);
        } elseif ($type == 2) {
            $data = $this->budgetReportPoolContractor($id_pool);
        } else {
            return [
                "message" => "Es necesario un tipo de descarga",
                "code" => 500,
            ];
        }

        $this->downloadExeclReportForPool($data);
    }

    private function downloadExeclReportForPool($data)
    {
        Excel::create('Laravel Excel', function ($excel) use ($data) {
            $excel->sheet('Productos', function ($sheet) use ($data) {
                $i = 1;
                $sheet->row($i, [
                    'ACTIVIDAD', 'UNIDAD', 'CANTIDAD', 'VALOR_UNITARIO', 'VALOR_TOTAL',
                ]);

                foreach ($data['data'] as $index => $user) {
                    $i = $i + 1;
                    $sheet->row($index + 2, [
                        $user['action_name'], $user['material_unit'], $user['budget_quantity'], $user['budget_value'], $user['budget_value_total'],
                    ]);
                }

                $sheet->row($i + 2, [
                    'COSTO_DIRECTO', 'ADMINISTRACION', 'UTILIDAD', 'IVA_SOBRE_UTILIDAD', 'ASISTENCIA_TECNICA', 'VALOR_TOTAL',
                ]);

                $sheet->row($i + 3, [
                    $data['budget_total'], $data['budget_adminstration'], $data['budget_utility'], $data['budget_iva'], $data['technical_assistance'], $data['value_total'],
                ]);

                $sheet->row($i + 5, [
                    'CUENCA', 'MUNICIPIO', 'PREDIO', 'COSTO_DIRECTO', 'PORCENTAJE', 'ADMINISTRACION', 'UTILIDAD', 'IVA_SOBRE_UTILIDAD', 'VALOR_TOTAL',
                ]);
                $i = $i + 6;
                $j = $i;
                foreach ($data['potential'] as $index => $user) {
                    $j = $j + 1;
                    $sheet->row($index + $i, [
                        $user['micro_basin'], $user['municipality'], $user['potential'], $user['budget_total'], $user['porcent'], $user['budget_adminstration'], $user['budget_utility'], $user['budget_iva'], $user['value_total'],
                    ]);
                }
                $sheet->row($j, [
                    " ", " ", " ", $data['budget_total'], " ", $data['budget_adminstration'], $data['budget_utility'], $data['budget_iva'], $data['value_total'],
                ]);
            });
        })->export('xls');
    }

    /**
     * @param  object task open
     * @param  $pool_id
     * @return mixed
     */
    private function addTaskOpenInPool($task_opens, $pool_id)
    {

        if (CvPoolProcess::where('pool_id', $pool_id)->exists()) {

            foreach ($task_opens as $key => $opens) {
                foreach ($opens as $taskopens) {
                    $validate_pool = CvPoolProcess::where('pool_id', $pool_id)->where('process_id', $key)->where('task_open_id', $taskopens);
                    $validate_task = CvPoolProcess::where('task_open_id', $taskopens);
                    if (!$validate_pool->exists() && !$validate_task->exists()) {
                        $newopen = new CvPoolProcess();
                        $newopen->pool_id = $pool_id;
                        $newopen->process_id = $key;
                        $newopen->task_open_id = $taskopens;
                        $newopen->save();
                        $this->CommitedAssociatedIsTaskOpen($taskopens);
                    }
                }
            }
        }
    }

    /**
     * @param $poolByProcess
     * @param $pool
     * @return mixed
     */
    private function asigmentContractorByActionsThePool($pool)
    {
        $contractor_action = 0;
        $poolByProcess = CvPoolProcess::where("pool_id", $pool);
        if ($poolByProcess->exists()) {
            foreach ($poolByProcess->get() as $actionUser) {
                $poolActionByUserValidate = CvPoolActionByUser::where("pool_by_process_id", $actionUser->id);
                if ($poolActionByUserValidate->exists()) {
                    $contractor_action = $poolActionByUserValidate->first()->user_id;
                }
            }
            //--- Valida si la accion esta asignada ---//
            if ($contractor_action != 0) {
                foreach ($poolByProcess->get() as $actionUser) {
                    $poolActionByUserValidate = CvPoolActionByUser::where("pool_by_process_id", $actionUser->id);
                    if ($poolActionByUserValidate->exists()) {
                        $poolActionByUser = $poolActionByUserValidate->first();
                    } else {
                        $poolActionByUser = new CvPoolActionByUser();
                    }
                    $poolActionByUser->pool_by_process_id = $actionUser->id;
                    $poolActionByUser->user_id = $contractor_action;
                    $poolActionByUser->save();
                }
            }
        }
    }

    /**
     * @param Request $request
     * @param $pool
     */
    public function addOrUpdateOtherCampsContractor(Request $request)
    {

        $add_other = CvOtherInfoContractor::where('pool_id', $request->id_pool)->get();
        if ($add_other->isNotEmpty()) {

            $add_other = CvOtherInfoContractor::find($add_other->first()->id);
        } else {

            $add_other = new CvOtherInfoContractor();
        }
        $add_other->infojson = json_encode($request->other_camps, true);
        $add_other->pool_id = $request->id_pool;
        $add_other->user_id = $request->user_id;
        $add_other->save();

        return [
            "message" => "datos almacenados",
            "code" => 200,
        ];
    }

    //agrega imprevisto//
    public function addUnforeseenTariffContractor(Request $request)
    {
        $add_other = CvUnforeseenTariffContractor::where('pool_id', $request->pool_id)->get();
        if ($add_other->isNotEmpty()) {

            $add_other = CvUnforeseenTariffContractor::find($add_other->first()->id);
        } else {

            $add_other = new CvUnforeseenTariffContractor();
        }
        $add_other->description = 'Imprevisto';
        $add_other->budget_contractor = $request->value;
        $add_other->pool_id = $request->pool_id;
        $add_other->save();

        return [
            "message" => "datos almacenados",
            "code" => 200,
        ];
    }

    public function getUnforeseenTariffContractor($id_pool)
    {
        return CvUnforeseenTariffContractor::where('pool_id', $id_pool)->first();
    }

    /**
     * @param $detail_task_open
     * @return mixed
     */
    private function typeAndRolTaskOpen($detail_task_open)
    {
        $detail_task_open['role_id'] = null;
        $detail_task_open['role_name'] = null;
        $task_budget = CvTaskOpenBudget::where('task_open_id', $detail_task_open->id);
        if ($task_budget->exists()) {

            $associated_contribution = CvAssociatedContribution::where('id', $task_budget->first()->associated_contributions_id);
            if ($associated_contribution->exists()) {

                $project_activitie = CvProjectActivity::where('id', $associated_contribution->first()->project_activity_id);
                if ($project_activitie->exists()) {
                    $coordination_activitie = CvActivityCoordination::where('activity_id', $project_activitie->first()->id);
                    if ($coordination_activitie->exists()) {

                        $detail_task_open['role_id'] = $coordination_activitie->first()->roleadd->id;
                        $detail_task_open['role_name'] = $coordination_activitie->first()->roleadd->name;
                    }
                }
            }
        }

        switch ($detail_task_open->task_open_sub_type_id) {
            case 1:
                $detail_task_open['type'] = "abierta";
                break;
            case 2:
                $detail_task_open['type'] = "psa";
                break;
            case 3:
            case 26:
            case 27:
            case 28:
            case 29:
            case 30:
                $detail_task_open['type'] = "erosivo";
                break;
            case 4:
            case 21:
            case 22:
            case 23:
            case 24:
            case 25:
                $detail_task_open['type'] = "hidrico";
                break;
            case 5:
            case 6:
            case 7:
            case 8:
                $detail_task_open['type'] = "comunicacion";
                break;
            case 18:
            case 19:
            case 20:
                $detail_task_open['type'] = "contratista";
                break;
            case 31:
            case 32:
            case 33:
            case 34:
            case 35:
            case 36:
            case 37:
            case 38:
                $detail_task_open['type'] = "especial";
                break;
            default:
                $detail_task_open['type'] = "na";
                break;
        }
        return $detail_task_open;
    }

    private function CommitedAssociatedIsTaskOpen($id_task_open)
    {
        $task_budget = CvTaskOpenBudget::where('task_open_id', $id_task_open);
        if ($task_budget->exists()) {
            foreach ($task_budget->get() as $budget) {
                $contribution = CvAssociatedContribution::find($budget->associated_contributions_id);
                $contribution->committed = $contribution->committed + $budget->amount;
                $contribution->committed_balance = $contribution->balance - $contribution->committed;
                $contribution->save();
            }
        }
    }

}
