<?php

namespace App\Http\Controllers\General;

use App\CvTypeContractBolsa;
use App\Http\Controllers\Controller;

class GeneralContractController extends Controller
{
    public function getContracts(){
        $typeContracts = CvTypeContractBolsa::all();
        foreach ($typeContracts as $typeContract) {
            $typeContract->files;
        }
        return $typeContracts;
    }
}
