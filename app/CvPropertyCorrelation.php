<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvPropertyCorrelation extends Model {

    protected $table = "cv_property_correlation";

    //Consultar los tipos de predio
    public function consultPropertyCorrelation() {

        return CvPropertyCorrelation::all();
    }

    //Relaciones
    public function property()
    {
     return $this->hasMany(CvProperty::class, 'property_correlation_id');
    }

}
