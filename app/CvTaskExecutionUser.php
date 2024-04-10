<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvTaskExecution;
use App\CvPoolActionByUser;
use App\CvTaskStatus;

class CvTaskExecutionUser extends Model {

    protected $table = "cv_task_execution_by_user";
    public $timestamps = false;

    public function taskExecution() {
        return $this->belongsTo(CvTaskExecution::class, "task_id", "id");
    }

    public function taskStatus() {
        return $this->hasOne(CvTaskStatus::class, "task_status_id", "id");
    }

    public function actionByUserContractor() {
        return $this->belongsTo(CvPoolActionByUser::class, "pool_contractor_id", "id");
    }
}
