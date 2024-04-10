<?php

namespace App\Http\Controllers\Admin;

use App\CvActivityCoordination;
use App\CvProjectActivity;
use App\CvProjectByActivity;
use App\CvRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActivitieController extends Controller
{
    public function createActivitie(Request $data)
    {
        $activitie = new CvProjectActivity();
        $activitie->name = $data->name;
        $activitie->save();

        $add_coordination = new CvActivityCoordination();
        $add_coordination->role_id = $data->id_role;
        $add_coordination->activity_id = $activitie->id;
        $add_coordination->save();

        $add_project = new CvProjectByActivity();
        $add_project->project_id = $data->id_project;
        $add_project->activity_id = $activitie->id;
        $add_project->save();

        return [
            "message"=>'Registro de proyecto',
            "code"=>200
        ];
    }


    public function allActivitie()
    {
        $activitie= CvProjectActivity::all();
        foreach ($activitie as $detail){
            $detail->projectByActivity;
            foreach ($detail->action as $item) {
                $item->byMaterial->budgetPriceMaterial;
            }
            $role= $detail->bycoordination['role_id'];
            $detail['role']=CvRole::find($role);
        }
        return $activitie;
    }

    public function detailActivitie($id_activities)
    {
        $programs=  CvProjectActivity::find($id_activities);
        $programs->projectByActivity;
        $role= $programs->bycoordination['role_id'];
        $programs['role']=CvRole::find($role);
        return $programs;
    }

    public function updateActivitie(Request $data)
    {
        $activitie=CvProjectActivity::find($data->activitie_id);
        $activitie->name=$data->name;
        $activitie->save();

        $add_coordination=CvActivityCoordination::where('activity_id',$data->activitie_id)->first();
        $add_coordination->role_id=$data->id_role;
        $add_coordination->activity_id=$activitie->id;
        $add_coordination->save();

        $add_project=CvProjectByActivity::where('activity_id',$data->activitie_id)->first();
        $add_project->program_id=$data->id_program;
        $add_project->project_id=$activitie->id;
        $add_project->save();

        return [
            "message"=>'Registro de proyecto',
            "code"=>200
        ];
    }
}
