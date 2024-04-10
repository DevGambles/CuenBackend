<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;
use App\CvRole;

class CvActivityCoordination extends Model {

    protected $table = "cv_activity_by_coordination";
    public $timestamps = false;

    public function Activity() {
        return $this->hasMany(CvAssociatedContribution::class, 'project_activity_id', 'activity_id');
    }

    public function roleadd() {
        return $this->belongsTo(CvRole::class, 'role_id');
    }

}
