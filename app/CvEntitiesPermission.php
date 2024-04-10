<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvEntities;
use App\CvPermission;

class CvEntitiesPermission extends Model {

    protected $table = "cv_entities_permission";

    public function entities() {
        return $this->belongsTo(CvEntities::class);
    }

    public function permission() {
        return $this->belongsTo(CvPermission::class);
    }

}
