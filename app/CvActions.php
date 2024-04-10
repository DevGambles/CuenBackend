<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvContributionPerShare;
use App\CvActionType;
use App\CvActionByActivity;

class CvActions extends Model {

    protected $table = "cv_actions";

    public function actions() {
        return $this->belongsTo(CvContributionPerShare::class, 'actions_id', 'id');
    }

    public function types() {
        return $this->belongsToMany(CvActionType::class,'cv_action_by_types', 'action_id', 'type_id');
    }

    public function byActivite() {
        return $this->hasOne(CvActionByActivity::class,'action_id');
    }

    public function byMaterial() {
        return $this->hasOne(CvBudgetActionMaterial::class,'action_id');
    }

}
