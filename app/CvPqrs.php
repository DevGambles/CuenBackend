<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvResponsePqrs;

class CvPqrs extends Model {

    protected $table = "cv_pqrs";

    //*** Relaciones ***//

    public function responsePqrs() {
        return $this->hasMany(CvResponsePqrs::class, 'pqrs_id');
    }
    public function rol() {
        return $this->hasOne(CvRolePqrs::class ,'id', 'dependencies_role_id');
    }

}
