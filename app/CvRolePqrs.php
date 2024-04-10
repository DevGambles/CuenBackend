<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvRole;

class CvRolePqrs extends Model {

    protected $table = "cv_role_pqrs";

    public function role() {
        return $this->belongsTo(CvRole::class, 'dependencies_role_id', 'id');
    }

}
