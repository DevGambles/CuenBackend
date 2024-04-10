<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvGeoJson extends Model
{
    protected $table = "cv_geo_json";

    //--- Relaciones ---//
    public function task() {

        return $this->belongsTo(CvTask::class);
    }
}
