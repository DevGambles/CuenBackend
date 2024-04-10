<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProject;
use App\CvAssociatedContribution;
use App\CvTaskType;
use App\CvFinancierAction;
use App\CvActivityCoordination;

class CvProjectActivity extends Model {

    protected $table = "cv_project_activity";

    public function project() {
        return $this->belongsToMany(CvProject::class, 'cv_task_type_by_activity', 'task_type_id', 'activity_id');
    }

    public function associatedContribution() {
        return $this->hasMany(CvAssociatedContribution::class, 'project_activity_id');
    }

    public function projectByActivity() {
        return $this->belongsToMany(CvProject::class, 'cv_project_by_activity', 'activity_id', 'project_id');
    }

    public function taskTypeByActivity() {
        return $this->belongsToMany(CvTaskType::class, 'cv_task_type_by_activity', 'task_type_id', 'activity_id');
    }

    public function financerAction() {
        return $this->hasMany(CvFinancierAction::class, 'activity_id');
    }

    public function bycoordination() {
        return $this->hasOne(CvActivityCoordination::class, 'activity_id');
    }

    public function action() {
        return $this->belongsToMany(CvActions::class, 'cv_actions_by_activity', 'activity_id', 'action_id');
    }


}
