<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudget;
use App\CvPoolActionByUser;
use App\CvProcess;

class CvPoolProcess extends Model {

    protected $table = "cv_pool_by_process";
    public $timestamps = false;

    public function poolByProcessBudget() {

        return $this->belongsToMany(CvBudget::class, "cv_pool_by_process", "budget_id");
    }

    public function poolActionsByUserContractor() {

        return $this->hasOne(CvPoolActionByUser::class, 'pool_by_process_id', 'id');
    }

    public function Budget() {

        return $this->belongsTo(CvBudget::class, "budget_id");
    }

    public function Process() {

        return $this->belongsTo(CvProcess::class, "process_id");
    }

    public function openTaskBudget(){
        return $this->hasOne(CvTaskOpenBudget::class, 'task_open_id', 'task_open_id');
    }

    public function openTask(){
        return $this->hasOne(CvTaskOpen::class, 'id', 'task_open_id');
    }

    public function contractor(){
        return $this->hasOne(CvBudgetByBudgetContractor::class, 'budget_id', 'budget_id');
    }
}
