<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvTask;

class CvTaskStatus extends Model {

    protected $table = "cv_task_status";

    //Relaciones
    public function task()
    {
     return $this->hasMany(CvTask::class, 'task_status_id');
    }

}
