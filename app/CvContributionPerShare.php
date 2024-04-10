<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociated;
use App\CvActions;
use App\CvBudget;

class CvContributionPerShare extends Model {

    protected $table = "cv_contribution_per_shares";

    public function associateds() {
        return $this->hasMany(CvAssociated::class, 'associated_id', 'id');
    }

    public function actions() {
        return $this->hasMany(CvActions::class, 'actions_id', 'id');
    }

    public function budget() {
        return $this->belongsTo(CvBudget::class, 'budget_id', 'id');
    }

    public function associate() {
        return $this->belongsTo(CvAssociated::class, 'associated_id', 'id');
    }

}
