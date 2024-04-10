<?php

namespace App\Http\Controllers\Admin;

use App\CvProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProgramsController extends Controller
{
    public function createProgram(Request $data)
    {
        $program=new CvProgram();
        $program->name=$data->name;
        $program->save();
        return [
            "message"=>'Registro de programa',
            "code"=>200
        ];
    }


    public function allProgram()
    {
        return CvProgram::all();
    }

    public function detailProgram($id_program)
    {
        $programs=  CvProgram::find($id_program);
        $programs->project;
        return $programs;
    }

    public function updateProgram(Request $data)
    {
        $program=CvProgram::find($data->program_id);
        $program->name=$data->name;
        $program->save();
        return [
            "message"=>'Registro de programa',
            "code"=>200
        ];
    }
}
