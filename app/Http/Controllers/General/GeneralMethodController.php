<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvDepartament;

class GeneralMethodController extends Controller {

    //*** Consultar departamentos ***//
    
    public function departaments() {
        return CvDepartament::all();
    }
    
    //*** Consultar los municipios de un departamento en especifico ***//

    public function municipality($departament_id) {
        $municipio = CvDepartament::find($departament_id);
        return $municipio->municipality()->orderBy('name', 'asc')->get();
    }

}
