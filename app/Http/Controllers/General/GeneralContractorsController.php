<?php

namespace App\Http\Controllers\General;

use App\CvAssociatedContribution;
use App\CvBackupContribution;
use App\CvBudget;
use App\CvBudgetByBudgetContractor;
use App\CvContractorBudgetDetailOrigin;
use App\CvDetailOriginResource;
use App\CvOriginResource;
use App\CvProcess;
use App\CvTariffActionContractor;
use App\CvTaskExecution;
use App\CvTaskOpen;
use App\CvTaskOpenBudget;
use App\CvTaskOpenByTaskExecution;
use App\CvUnionOfProcess;
use App\Http\Middleware\RoleEntitiesPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvCategory;
use App\CvCategoryByContractor;
use App\CvContractor;
use App\CvFormatDetallCotractor;
use App\CvFormatSowingCotractor;
use App\CvFormatBySowing;
use App\CvProperty;
use App\CvPool;
use Sami\Parser\Filter\TrueFilter;

class GeneralContractorsController extends Controller {

    //Consulta todas las categorias
    public function category_all() {
        return CvCategory::all();
    }

    //con el id de la categoria consulta los contratistas pertenecientes a dicha categoria
    public function contractorCategory($id_category) {
        $category = CvCategory::find($id_category)->categoryByContractor->where('role_id', 5);
        return $category;
    }

    public function contractorFormat(Request $contractor, $id_task) {

        $validate=CvFormatDetallCotractor::where('task_id',$id_task)->exists();
        if ($validate){
            return [
                "message" => "Ya existe un registro del formulario",
                "code" => 500
            ];
        }

        $formatContractor = new CvFormatDetallCotractor();
        $formatContractor->form_contractor = json_encode( $contractor->all(), true);

        $formatContractor->user_id = $this->userLoggedInId(); //contratista
        $formatContractor->task_id = $id_task; //contratista

        try {
            $formatContractor->save();


            return [
                "message" => "Formato de contrato almacenado",
                "code" => 200
            ];
        } catch (Exception $exc) {
            return [
                "message" => "Algo salio mal",
                "code" => 500
            ];
        }
    }

    public function contractorSowing(Request $sowing, $id_task) {

        $contractore=CvFormatDetallCotractor::where('task_id',$id_task)->first();

        foreach ($sowing->data as $data){
            foreach ($data['form'] as $form){
                $formatSowing = new CvFormatSowingCotractor();
                $formatSowing->	form_sowing = json_encode( $form, true);
                $formatSowing->user_id = $this->userLoggedInId();
                $formatSowing->	hash = $data['hash'];
                $formatSowing->save();

                $bysowing = new CvFormatBySowing();
                $bysowing->detall_contractor_id = $contractore->id;
                $bysowing->detall_sowing_id = $formatSowing->id;
                $bysowing->save();
            }

        }

        return [
            "message" => "Formato de contrato de siembras almacenado",
            "code" => 200
        ];

    }

    public function detallFormatContractor($id_pool) {
        $pool = CvPool::find($id_pool);
        if ($pool) {
            return $pool->formatContractor->sowingByFormat;
        } else {
            return [
                "message" => "La bolsa no existe",
                "code" => 500
            ];
        }
    }

    public function consecutive() {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    }

    public function getGeoMap($id_task)
    {
        $geomap= new GeneralTaskExecutionController();
        $task=CvTaskOpen::find($id_task);
        $execution_open=CvTaskOpenByTaskExecution::where('task_open',$task->id)->first();
        return  $geomap->getGeoMapTaskExecution($execution_open->task_execution);

    }

    //Une procedimientos
    public function unionProcess(Request $request)
    {
        $father= CvProcess::find($request->process_father_id);
        $son=CvProcess::find($request->process_son_id);

        $validate =$this->validateProcessSonFather($son->id, $father->id);

        if ($validate == 0){

            $unionProces=new CvUnionOfProcess();
            $unionProces->process_father_id=$father->id;
            $unionProces->process_son_id=$son->id;
            $unionProces->save();
            return [
                "message" => "Se ha unido el procedimiento",
                "code" => 200
            ];
        }else{
            return $validate;
        }
    }
    //Trae los procedimientos que pueden ser padre
    public function getParentProcess()
    {
        $info=array();
        $process_all= CvProcess::all();
        foreach ($process_all as $process){
            if (!CvUnionOfProcess::where('process_son_id',$process->id)->exists()){
                array_push($info,$process);
            }
        }
        return $info;
    }
    //Trae los procedimientos padre con sus hijos anidados
    public function getunionProcess()
    {
        $info= array();
        $parents_all= $this->getParentProcess();
        foreach ($parents_all as $parent){
            $proce= CvProcess::find($parent['id']);
            $proce->processSons;
            if (CvUnionOfProcess::where('process_father_id',$parent['id'])->exists()){
                array_push($info,$proce);
            }
        }
        return $info;
    }

    private function validateProcessSonFather($son,$father){
        $validate=0;
        //los procedimientos deben ser diferentes
        if ($son == $father)
        {
            $validate=1;
            return [
                "message" => "No puede anidar el mismo procedimiento",
                "code" => 500
            ];
        }
        //El procedimiento que entra de hijo no puede ser padre
        if (CvUnionOfProcess::where('process_father_id',$son)->exists())
        {
            $validate=1;
            return [
                "message" => "El procedimiento ya es principal",
                "code" => 500
            ];
        }
        //El procedimiento que entra de hijo no puede existir como hijo
        if (CvUnionOfProcess::where('process_son_id',$son)->exists())
        {
            $validate=1;
            return [
                "message" => "El procedimiento ya esta encadenado",
                "code" => 500
            ];
        }

        //El procedimiento que entra de padre no puede ser hijo de otros procedimientos
        if (CvUnionOfProcess::where('process_son_id',$father)->exists())
        {
            $validate=1;
            return [
                "message" => "El procedimiento ya es principal",
                "code" => 500
            ];
        }

        return $validate;
    }

    public function insertTarifContracrtorMoneyAction(Request $data)
    {

        if ($data->action_value <= 0){
            return[
                'message'=>'El costo asignado no es valido',
                'code'=>500
            ];
        }
        $pool=CvPool::find($data->pool_id);

        $other_camps= $pool->infoContractor;

        $validate_tariff=CvTariffActionContractor::where('user_id',$other_camps->user_id)->where('action_id',$data->action_id);
        if ($validate_tariff->exists()){
            $tariff_contract= CvTariffActionContractor::find($validate_tariff->first()->id);
            if ($data->action_value < $tariff_contract->budget_contractor){
                return[
                    'message'=>'El costo asignado es menor a lo ya establecido',
                    'code'=>500
                ];
            }
        }else{
            $tariff_contract= new CvTariffActionContractor();
        }
        $tariff_contract->budget_contractor=$data->action_value;
        $tariff_contract->user_id=$other_camps->user_id;
        $tariff_contract->action_id=$data->action_id;
        $tariff_contract->budget_prices_material_id=$data->material_id;
        $tariff_contract->save();
        foreach ($data->budget as $asbudget){

            $budget=CvBudget::find($asbudget);
            $validate_budget=CvBudgetByBudgetContractor::where('budget_id', $budget->id)->where('contractor_id',$other_camps->user_id);
            if ($validate_budget->exists()){
                $budget_contractor= CvBudgetByBudgetContractor::find($validate_budget->first()->id);
            }else{
                $budget_contractor=new CvBudgetByBudgetContractor();
            }
            $budget_contractor->price_contractor= $budget->length * $data->action_value;
            $budget_contractor->budget_contractor= $data->action_value;
            $budget_contractor->tariff_id= $tariff_contract->id;
            $budget_contractor->budget_id= $budget->id;
            $budget_contractor->contractor_id= $other_camps->user_id;
            $budget_contractor->save();
        }


        return[
            'message'=>'Tarifa creada por contratista',
            'code'=>200
        ];
    }

    public function budgetContractor(Request $data)
    {

        for ($i=0;$i < count($data->all());$i++){
            $total_contri=0;
            foreach ($data[$i]['contributions'] as $contribution_detal) {
                $origin_value_contri =$contribution_detal['contribution_value'];
                $total_contri=$total_contri + $origin_value_contri;
            }
            if ($data[$i]['action_value'] < $total_contri){
                return[
                    'message'=>'La suma de lo valores dados es mayor al costo de la accion',
                    'code'=>500
                ];
            }
        }

        for ($i=0;$i < count($data->all());$i++){
            if ($data[$i]['type']=="task_open"){
                foreach ($data[$i]['contributions'] as $contribution_detal) {
                    $task_budget_id =$contribution_detal['task_budget_id'];
                    $origin_value =$contribution_detal['contribution_value'];
                    $origin_contribution_id =$contribution_detal['contribution_id'];
                    //Afectar comando y control de presupuesto dado de contratista por accion
                    $resource=CvTaskOpenBudget::find($task_budget_id);
                    $contribution=CvAssociatedContribution::find($origin_contribution_id);

                    //Quita el comprometido dado en origen de los recursos
                    if($resource->amount == null || $resource->amount == ''){
                        $descont=0;
                    }else{
                        $descont=$resource->amount;
                    }
                    $contribution->committed=$contribution->committed-$descont;
                    if ($contribution->committed <= 0){
                        $contribution->committed=0;
                    }

                    //suma el comprometido del nuevo presupuesto de contratista
                    $contribution->committed=$contribution->committed + $origin_value;
                    $resource->amount=$origin_value;
                    //Calcula el compometido disponible
                    $contribution->committed_balance=$contribution->balance-$contribution->committed;

                    $resource->save();
                    $contribution->save();
                }
            }else{
                foreach ($data[$i]['contributions'] as $contribution_detal) {
                    $contracor=$contribution_detal['contractor_id'];
                    $budget_id = $contribution_detal['budget_id'];
                    $origin_id =$contribution_detal['origin_id'];
                    $origin_value =$contribution_detal['contribution_value'];
                    $origin_contribution_id =$contribution_detal['contribution_id'];
                    $budget_contractor=CvBudgetByBudgetContractor::where('budget_id',$budget_id)->where('contractor_id',$contracor)->first();
                    //Afectar comando y control de presupuesto dado de contratista por accion
                    $resource=CvDetailOriginResource::where('contribution_id',$origin_contribution_id)->where('budget_id',$budget_id)->first();
                    $contribution=CvAssociatedContribution::find($origin_contribution_id);

                    //Quita el comprometido dado en origen de los recursos
                    if($resource->ultimate_committed == null || $resource->ultimate_committed == ''){
                        $descont=$resource->value;
                    }else{
                        $descont=$resource->ultimate_committed;
                    }
                    $contribution->committed=$contribution->committed-$descont;
                    if ($contribution->committed <= 0){
                        $contribution->committed=0;
                    }

                    //suma el comprometido del nuevo presupuesto de contratista
                    $contribution->committed=$contribution->committed + $origin_value;
                    $resource->ultimate_committed=$origin_value;
                    //Calcula el compometido disponible
                    $contribution->committed_balance=$contribution->balance-$contribution->committed;
                    $validateOriginContractor=CvContractorBudgetDetailOrigin::where('contribution_id',$contribution->id)->where('budget_contractor_id', $budget_contractor->id);
                    if ($validateOriginContractor->exists()){
                        $origin_contractor= $validateOriginContractor->first();
                    }else{
                        $origin_contractor= new CvContractorBudgetDetailOrigin();
                    }

                    $origin_contractor->ultimate_committed=$origin_value;
                    $origin_contractor->value=$origin_value;
                    $origin_contractor->contribution_id=$contribution->id;
                    $origin_contractor->associated_id=$contribution->associated_id;
                    $origin_contractor->budget_contractor_id=$budget_contractor->id;

                    $origin_contractor->save();
                    $resource->save();
                    $contribution->save();
                }
            }
        }
        return 1;
    }

    public function getbudgetContractor($budget_id,$contractor)
    {

        $budget= CvBudgetByBudgetContractor::where('budget_id',$budget_id)->where('contractor_id',$contractor)->first();

        return $budget;
    }


}
