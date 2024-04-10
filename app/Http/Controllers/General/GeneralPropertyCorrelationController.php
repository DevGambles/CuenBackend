<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvPropertyCorrelation;

class GeneralPropertyCorrelationController extends Controller {

    //Consultar los tipos de predio
    public function consultPropertyCorrelationData() {

        $propertyCorrelation = new CvPropertyCorrelation();

        return $propertyCorrelation->consultPropertyCorrelation();
    }

}
