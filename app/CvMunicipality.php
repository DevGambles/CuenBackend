<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvDepartament;

class CvMunicipality extends Model {

    protected $table = "cv_municipality";

    public function actionsMaterials() {
        return $this->belongsTo(CvDepartament::class, "departament_id");
    }

}
