<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudget;
use App\CvPoolProcess;
use App\CvFormatDetallCotractor;
use App\CvOtherInfoContractor;

class CvPool extends Model {

    protected $table = "cv_pool";

    public function poolByProcess() {

        return $this->hasMany(CvPoolProcess::class, 'pool_id');
    }

    public function poolByBudgetPivot() {

        return $this->belongsToMany(CvBudget::class, 'cv_pool_by_process', 'pool_id', 'budget_id');
    }

    public function formatContractor() {

        return $this->hasOne(CvFormatDetallCotractor::class, 'pool_id');
    }

    public function contract() {
        return $this->hasMany(CvContract::class, 'pool_id');
    }

    public function infoContractor() {
        return $this->hasOne(CvOtherInfoContractor::class, 'pool_id');
    }

}
