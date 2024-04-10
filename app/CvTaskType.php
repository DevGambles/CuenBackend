<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProjectActivity;

class CvTaskType extends Model {

    protected $table = "cv_task_type";

    public static function consultTypeTask() {

        return CvTaskType::all();
    }

    // *** Relaciones *** //
    public function task() {
        return $this->hasMany(CvTask::class, 'task_type_id');
    }

    public function taskTypeByActivity() {
        return $this->belongsToMany(CvProjectActivity::class, 'cv_task_type_by_activity', 'task_type_id', 'activity_id');
    }

}
