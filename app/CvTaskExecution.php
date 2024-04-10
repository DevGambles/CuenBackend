<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvTaskExecutionUser;
use App\CvTaskExecutionGeoMap;

class CvTaskExecution extends Model {

    protected $table = "cv_task_execution";

    public function taskExecutionByUser() {
        return $this->hasOne(CvTaskExecutionUser::class, 'task_id', 'id');
    }

    public function subtypes(){
        return $this->belongsTo(CvTaskOpenSubType::class,'task_open_sub_type_id');
    }
    public function geoMapLoad(){
        return $this->hasMany(CvTaskExecutionGeoMap::class,'task_execution_id');
    }

    public function status(){
        return $this->belongsTo(CvTaskStatus::class, 'task_status_id');
    }

    public function poolByProcess(){
        return $this->hasOne(CvPoolActionByUser::class,'id', 'pool_actions_contractor_id');
    }

}
