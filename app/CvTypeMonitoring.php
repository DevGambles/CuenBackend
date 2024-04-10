<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvTypeMonitoring extends Model {

    protected $table = "cv_type_monitoring";

    //--- Consultar todos los tipos de monitoreo ---//

    public static function consultAllTypesMonitoring() {
        return CvTypeMonitoring::get();
    }

}
