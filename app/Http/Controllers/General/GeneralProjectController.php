<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvProject;

class GeneralProjectController extends Controller {

    //Consultar los proyectos

    public function consultProjects() {
        return CvProject::all();
    }

}
