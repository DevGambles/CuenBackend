<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProjectActivity;
use App\CvTask;
use App\CvPool;
use App\CvMonitoring;
use App\CvLetterIntention;
use App\CvPotentialProperty;
use App\CvTaskOpen;

class CvProcess extends Model {

    protected $table = "cv_process";

    // *** Relaciones *** //

    public function processByProjectByActivity() {
        return $this->belongsToMany(CvProjectActivity::class, 'cv_process_by_activity', 'process_id', 'project_activity_id');
    }

    public function processByTasks() {
        return $this->belongsToMany(CvTask::class, 'cv_task_by_process', 'process_id', 'task_id');
    }

    public function processPool() {
        return $this->belongsToMany(CvPool::class, 'cv_pool_by_process', 'process_id', 'pool_id');
    }

    public function monitoring() {
        return $this->hasOne(CvMonitoring::class, 'process_id');
    }

    public function letterIntention() {
        return $this->hasOne(CvLetterIntention::class, 'process_id');
    }

    public function processSons() {
        return $this->belongsToMany(CvProcess::class, 'cv_union_of_processes', 'process_father_id', 'process_son_id');
    }

    public function processFhater() {
        return $this->belongsToMany(CvProcess::class, 'cv_union_of_processes', 'process_son_id', 'process_father_id');
    }

    public function potentialProperty(){
        return $this->belongsTo(CvPotentialProperty::class,'potential_property_id');
    }

    public function poolByProcess(){
        return $this->hasMany(CvPoolProcess::class, 'process_id');
    }

    public function taskOpenProcess(){
        return $this->hasMany(CvTaskOpen::class, 'process_id');
    }

    public function originResource(){
        return $this->hasMany(CvOriginResource::class, 'process_id');
    }

}
