<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvTypeContract;
use App\CvContractorModality;
use App\CvRole;
use App\CvCategory;

class GeneralUserController extends Controller {

    //--- Consultas para la información del usuario contratista---//

    /*
     *  Consultar los tipos de contratos
     */

    public function consultTypeContractor() {

        return CvTypeContract::get();
    }

    /*
     *  Consultar las modalidades del contrato
     */

    public function consultModalityContractor() {

        return CvContractorModality::get();
    }

    /*
     *  Consultar todos los roles de los usuarios
     */

    public function consultRole() {

        return CvRole::get();
    }

    public function category() {

        return CvCategory::get();
    }

    public function delete_category(Request $request) {
        CvCategory::where('id', $request->name)->delete();
    }

}
