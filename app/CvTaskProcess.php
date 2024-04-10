<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProject;
use App\CvTask;

class CvTaskProcess extends Model {

    protected $table = "cv_task_by_process";
    public $timestamps = false;

    public function project() {

        return $this->belongsTo(CvProject::class);
    }

    public function task() {

        return $this->belongsTo(CvTask::class);
    }

}
