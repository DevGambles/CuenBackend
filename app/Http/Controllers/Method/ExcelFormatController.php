<?php

namespace App\Http\Controllers\Method;

use App\CvBudget;
use Illuminate\Http\Request;
use App\CvProcess;
use App\CvExcelFormatProperty;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\CvBudgetActionMaterial;
use App\CvActionByActivity;
use App\CvAssociatedContribution;
use App\CvAssociated;
use PhpParser\Node\Expr\New_;

class ExcelFormatController extends Controller
{

    public function formatPropertyExcel()
    {
        //Vacia tabla excel
        $this->deleteExelTable(CvExcelFormatProperty::all());

        //Consulta de formato
        $all_process= CvProcess::all();
        $i=1;
        $names_associate="";
        $name_associate_pr="";
        foreach ($all_process as $proces){
            $task=$proces->processByTasks->where('task_sub_type_id','>=',4)->first();
            if ($task){
                $associated = array();
                $property_info= json_decode($task->property->info_json_general, true) ;
                if (CvBudget::where('task_id',$task->id)->exists()){
                    foreach ($task->budget as $budget) {
                        $budgetaction = CvBudgetActionMaterial::find($budget->action_material_id);
                        //--Tipo de accion--//
                        $typesActivities = $budgetaction->action->types;
                        //Accion activa
                        $active= $this->butgetForTypeActivity($this->typeAction($typesActivities), 1 ,$proces,$budgetaction,$budget);
                        //Accion activa mantenimiento
                        $active_manteniment= $this->butgetForTypeActivity($this->typeAction($typesActivities), 2 ,$proces,$budgetaction,$budget);
                        //Accion pasiva
                        $pasive= $this->butgetForTypeActivity($this->typeAction($typesActivities), 3 ,$proces,$budgetaction,$budget);
                        //Accion buenas practicas
                        $practices= $this->butgetForTypeActivity($this->typeAction($typesActivities), 4 ,$proces,$budgetaction,$budget);

                    }

                    foreach ($active_manteniment['associates'] as $is_associate){
                        if ($this->insertDuplic($associated, $is_associate['id']) == FALSE) {
                            array_push($associated, array(
                                "name" => $is_associate['name'],
                                "id" => $is_associate['id'],
                            ));
                        }
                    }

                    foreach ($active['associates'] as $is_associate){
                        if ($this->insertDuplic($associated, $is_associate['id']) == FALSE) {
                            array_push($associated, array(
                                "name" => $is_associate['name'],
                                "id" => $is_associate['id'],
                            ));
                        }
                    }

                    foreach ($pasive['associates'] as $is_associate){
                        if ($this->insertDuplic($associated, $is_associate['id']) == FALSE) {
                            array_push($associated, array(
                                "name" => $is_associate['name'],
                                "id" => $is_associate['id'],
                            ));
                        }
                    }
                    foreach ($associated as $name_all){
                        $names_associate = $name_associate_pr. ",".$name_all['name'];
                    }
                    foreach ($practices['associates'] as $is_associate_pr){
                        $name_associate_pr = $name_associate_pr. ",".$is_associate_pr['name'];
                    }
                    if ($proces->potentialProperty->property_psa == 0){
                        $psa="no";
                    }else{
                        $psa="si";
                    }

                    if (array_key_exists('micro_basin',$property_info)){
                        $exel_property=  new CvExcelFormatProperty();
                        $exel_property->id=$i;
                        $exel_property->cuenca=$property_info['micro_basin'];
                        $exel_property->documento=$property_info['contact']['contact_id_card_number'];
                        $exel_property->propietario=$property_info['contact']['contact_id_card_number'];
                        $exel_property->municipio=$property_info['municipality'];
                        $exel_property->vereda=$property_info['lane'];
                        $exel_property->predio=$proces->potentialProperty->property_name;
                        $exel_property->area_predio_ficha=$property_info['economic_activity_in_the_property']['property_area'];
                        $exel_property->year=$property_info['property_visit_date']['year'];
                        $exel_property->activa=$active['total'];
                        $exel_property->pasiva=$pasive['total'];
                        $exel_property->mantenimiento=$active_manteniment['total'];
                        $exel_property->buenas_practicas=$practices['total'];
                        $exel_property->aportante=$names_associate;
                        $exel_property->aportante_buenas_practicas=$name_associate_pr;
                        $exel_property->estado=$task->taskSubType->name;
                        $exel_property->psa=$psa;
                        $exel_property->total_acuerdo= $exel_property->activa+ $exel_property->pasiva +$exel_property->mantenimiento+   $exel_property->buenas_practicas;
                        $exel_property->save();
                    }
                }
                $i=$i+1;
                $names_associate="";
                $name_associate_pr="";
                unset($associated);

            }
        }

        //Retorna Excel de formato predio
        $this->getExcelFormatProperty();
    }
    public function getExcelFormatProperty()
    {
        Excel::create('Laravel Excel', function($excel) {
            $excel->sheet('Productos', function($sheet) {
                $products = CvExcelFormatProperty::all();
                $sheet->fromArray($products);
            });
        })->export('xls');
    }

    private function insertDuplic($associated, $id) {

        if (count($associated) > 1) {
            for ($i = 0; $i < count($associated); $i++) {
                if ($associated[$i]["id"] == $id) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function butgetForTypeActivity($isActivite, $typeActivite, $proces,$budgetaction,$budget)
    {
        $total=0;
        $associated = array();
        if ($isActivite == $typeActivite) {

            $activis = CvActionByActivity::where('action_id', $budgetaction->action->id);
            if ($activis->exists()){
                $activitie=$activis->first();
                foreach ($proces->processByProjectByActivity as $activities) {
                    $contriAssociated = CvAssociatedContribution::where('project_activity_id', $activities->id);
                    if ($contriAssociated->exists()) {
                        //si hay inversionistas en la actividad, guarda en el array los asociados que invierten en la actividad
                        foreach ($contriAssociated->get() as $value) {
                            //validar este punto
                            if ($activitie->activity_id ==
                                $value->project_activity_id &&
                                $value->type == 1) {
                                $total= $total+ $budget->value;
                                array_push($associated, array(
                                    "id" => $value->associated_id,
                                    "name" => CvAssociated::find($value->associated_id)->name,
                                    "contribution_associated_id" => $value->id,
                                ));
                            }
                        }
                    }
                }
            }
        }
        return[
            "total"=> $total,
            "associates"=>$associated
        ];

    }

    private function deleteExelTable($table) {
        //elimina  la tabla excel
        $exel = $table;
        foreach ($exel as $value) {

            $value->delete();
        }
    }
    private function typeAction($param) {

        foreach ($param as $value) {
            if ( $value->id == 8 || $value->id == 9) {
                return 1; //ACTIVA
            } else if ($value->id == 5 || $value->id == 6 || $value->id == 7 ) {
                return 2; // ACTIVA MANTENIMIENTO
            }else if ($value->id == 1 || $value->id == 4) {
                return 3; //PASIVA
            } else if ($value->id == 10) {
                return 4; //BUENAS PRACTICAS
            }
        }
    }
}
