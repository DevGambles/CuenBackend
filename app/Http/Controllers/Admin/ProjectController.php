<?php

namespace App\Http\Controllers\Admin;

use App\CvProgramByProject;
use App\CvProject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function createProject(Request $data)
    {
        $project=new CvProject();
        $project->name=$data->name;
        $project->description=$data->description;
        $project->state=0;
        $project->save();

        $add_program=new CvProgramByProject();
        $add_program->program_id=$data->id_program;
        $add_program->project_id=$project->id;
        $add_program->save();

        return [
            "message"=>'Registro de proyecto',
            "code"=>200
        ];
    }


    public function allProject()
    {
        $modelProjects = CvProject::all();

        foreach ($modelProjects as $modelProject) {
            $modelProject->programByProject;
        }
        return $modelProjects ;
    }

    public function detailProject($id_project)
    {
        $programs=  CvProject::find($id_project);
        $programs->projectActities;
        $programs->typeProgram;
        return $programs;
    }

    public function updateProject(Request $data)
    {
        $project=CvProject::find($data->project_id);
        $project->name=$data->name;
        $project->description=$data->description;
        $project->state=0;
        $project->save();


        $add_program=CvProgramByProject::where('project_id',$data->project_id)->first();
        $add_program->program_id=$data->id_program;
        $add_program->project_id=$project->id;
        $add_program->save();

        return [
            "message"=>'Registro de proyecto',
            "code"=>200
        ];
    }
}
