<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvPropertyByUser extends Model {

    protected $table = "cv_potential_property_by_user";
    public $timestamps = false;

    public function property(){
        return $this->belongsTo(CvPotentialProperty::class)->where('potential_sub_type_id', '<>', 4);
    }
}
