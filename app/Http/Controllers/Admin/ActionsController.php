<?php

namespace App\Http\Controllers\Admin;

use App\CvActionByActivity;
use App\CvActionByType;
use App\CvActions;
use App\CvActionType;
use App\CvBudgetActionMaterial;
use App\CvBudgetPriceMaterial;
use App\CvUnits;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActionsController extends Controller
{
    public function createActions(Request $data)
    {
        $action=new CvActions();
        $action->name=$data->action_name;
        $action->good_practicess=$data->good_practicess;
        $action->type=$data->type_action;
        if ($action->type == 'area'){
            $action->color_fill=$data->color;
        }else{
            $action->color=$data->color;
        }
        $action->save();

        $material=new CvBudgetPriceMaterial();
        $material->name=$data->material_name;
        $material->price=$data->price;
        $material->type=$data->type_material;
        $material->measurement=$data->measurement;
        $material->unit_id=$data->unit_id;
        $material->save();

        $action_material=new CvBudgetActionMaterial();
        $action_material->action_id=$action->id;
        $action_material->budget_prices_material_id=$material->id;
        $action_material->save();

        $action_activite=new CvActionByActivity();
        $action_activite->action_id=$action->id;
        $action_activite->activity_id=$data->activite_id;
        $action_activite->save();

        $action_type=new CvActionByType();
        $action_type->action_id=$action->id;
        $action_type->type_id=$data->type_id;
        $action_type->save();

        return [
            "message"=>'Registro de acción',
            "code"=>200
        ];
    }

    public function allActions()
    {
        $activitie= CvActions::orderBy('id', 'desc')->get();
        foreach ($activitie as $item) {
            $item->byMaterial->budgetPriceMaterial;
        }
        return $activitie;
    }

    public function allTypesActions()
    {
        $types= CvActionType::where('id', '!=', 2)->where('id', '!=', 3)->get();
        return $types;
    }

    public function detailActions($id_action)
    {
        $action=  CvActions::find($id_action);
        $action['action_type']=  $action->types->where('id', '!=', 2)->where('id', '!=', 3)->first();
        $action['action_activite']=  $action->byActivite->Activiteadd;
        $action['action_material']=   $action->byMaterial->budgetPriceMaterial;
        
        return $action;
    }

    public function updateActions(Request $data)
    {
        $action=CvActions::find($data->id);
        $action->name=$data->name;
        $action->good_practicess=$data->good_practicess;
        $action->type=$data->type;
        if ($action->type == 'area'){
            $action->color_fill=$data->color;
        }else{
            $action->color=$data->color;
        }
        $action->save();

        $material=CvBudgetPriceMaterial::find($data->material['id']);
        $material->name= $data->material['name'];
        $material->price= $data->material['price'];
        $material->type= $data->material['type'];
        $material->measurement= $data->material['measurement'];
        $material->unit_id= $data->material['unit_id'];
        $material->save();

        $action_material=CvBudgetActionMaterial::where('action_id',$action->id)->first();
        $action_material->action_id=$action->id;
        $action_material->budget_prices_material_id=$material->id;
        $action_material->save();

        $action_activite=CvActionByActivity::where('action_id',$action->id)->first();
        $action_activite->action_id=$action->id;
        $action_activite->activity_id= $data->activityId;
        $action_activite->save();

        $action_type=CvActionByType::where('action_id',$action->id)->first();
        $action_type->action_id=$action->id;
        $action_type->type_id= $data->type_id;
        $action_type->save();

        return [
            "message"=>'Actualización de accion',
            "code"=>200
        ];
    }

    public function getAllUnitsMeasure(){
        return CvUnits::all();
    }
}
