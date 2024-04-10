<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudgetActionMaterial;
use App\CvTask;
use App\CvBudgetByBudgetContractor;
use App\CvOriginResource;

class CvBudget extends Model {

    protected $table = "cv_budget";

    public function actionsMaterials() {
        return $this->belongsTo(CvBudgetActionMaterial::class, "action_material_id");
    }

    public function task() {
        return $this->belongsTo(CvTask::class, "task_id");
    }
    public function budgetContractor() {
        return $this->hasOne(CvBudgetByBudgetContractor::class, "budget_id");
    }

    public function originResource() {
        return $this->hasOne(CvOriginResource::class, "budget_id");
    }

}
