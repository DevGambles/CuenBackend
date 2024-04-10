<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\CvTask;

class CvTaskUser extends Model {

    protected $table = "cv_task_by_user";
    public $timestamps = false;

    //*** Consulta de relaciones entre tablas ***//

    public function user() {

        return $this->belongsTo(User::class);
    }
    
    public function tasks() {

        return $this->belongsTo(CvTask::class);
    }

}
