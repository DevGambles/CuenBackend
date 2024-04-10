<?php

namespace App\Http\Controllers\Admin;

use App\CvFinancierCommandDetails;
use App\CvFinancierDetailCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LevelDetailController extends Controller
{
    public function createFinancierLevelDetail(Request $data)
    {
        $action=new CvFinancierDetailCode();
        $action->name=$data->detail_name;
        $action->code=$data->code;
        $action->actiion_id=$data->action_id;
        $action->save();

        return [
            "message"=>'Registro de detalle',
            "code"=>200
        ];
    }

    public function allFinancierLevelDetail()
    {
        $action= CvFinancierDetailCode::orderBy('id', 'desc')->get();

        return $action;
    }

    public function detailFinancierLevelDetail($id_action)
    {
        $action=  CvFinancierDetailCode::find($id_action);
        $action->financierDetail->detailCommandFinancier;
        return $action;
    }

    public function updateFinancierLevelDetail(Request $data)
    {
        $action=CvFinancierDetailCode::find($data->id);
        $action->name=$data->detail_name;
        $action->code=$data->code;
        $action->actiion_id=$data->action_id;
        $action->save();

        return [
            "message"=>'Actualización de detalle',
            "code"=>200
        ];
    }

    public function allFinancierCommandDetail()
    {
        $command=  CvFinancierCommandDetails::all();
        return $command;
    }
    public function detailFinancierCommandDetail($id_detail)
    {
        $command=  CvFinancierCommandDetails::find($id_detail);
        return $command;
    }

}
