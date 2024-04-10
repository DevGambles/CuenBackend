<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvMunicipality;

class CvDepartament extends Model {

    public function municipality() {
        return $this->hasMany(CvMunicipality::class, "departament_id");
    }

}
