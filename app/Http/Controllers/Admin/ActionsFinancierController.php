<?php

namespace App\Http\Controllers\Admin;

use App\CvFinancierAction;
use App\CvFinancierDetailCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ActionsFinancierController extends Controller
{
    public function createFinancierActions(Request $data)
    {
        $action=new CvFinancierAction();
        $action->name=$data->action_name;
        $action->code=$data->code;
        $action->activity_id=$data->activity_id;
        $action->save();

        return [
            "message"=>'Registro de acción',
            "code"=>200
        ];
    }

    public function allFinancierActions()
    {
        $action= CvFinancierAction::orderBy('id', 'desc')->get();

        return $action;
    }

    public function getActionsByActivityId($activityId)
    {
        $action = CvFinancierAction::where('activity_id', $activityId)
            ->orderBy('id', 'desc')->get();

        return $action;
    }

    public function detailFinancierActions($id_action)
    {
        $action=  CvFinancierAction::find($id_action);
        $action->financierDetail;
        return $action;
    }

    public function updateFinancierActions(Request $data)
    {
        $action = CvFinancierAction::findOrFail($data->id);
        $action->name = $data->name;
        $action->code = $data->code;

        if ($action->save()) {
            foreach ($data->financier_detail as $financialDetail) {

                $detailAction = CvFinancierDetailCode::findOrFail($financialDetail['id']);
                $detailAction->name = $financialDetail['name'];
                $detailAction->code = $financialDetail['code'];

                $detailAction->save();
            }

            return response()->json(
                [
                    "message" => 'Actualización de accion',
                    "code" => 200
                ], 200);

        } else {
            return response()->json(
                [
                    "message" => 'Ha ocurrido un error al actualizar la accion',
                    "code" => 429
                ], 429);
        }
    }

}
