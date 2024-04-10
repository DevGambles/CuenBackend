<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProjectActivity;
use App\CvAssociated;
use App\CvBackupContribution;
use App\CvContributionSpecies;
use App\CvFinacierCommandDetail;
use App\CvDetailOriginResource;

class CvAssociatedContribution extends Model {

    protected $table = "cv_associated_contributions";

    public function projectActivity() {
        return $this->belongsTo(CvProjectActivity::class, 'project_activity_id');
    }

    public function associated() {
        return $this->belongsTo(CvAssociated::class, 'associated_id', 'id');
    }

    public function thisisassociate() {
        return $this->hasOne(CvAssociated::class,'id', 'associated_id');
    }


    public function logContribution() {
        return $this->hasMany(CvBackupContribution::class, 'contribution_id');
    }

    public function species() {
        return $this->hasMany(CvContributionSpecies::class, 'contributions_id');
    }
    public function financierDetail() {
        return $this->hasMany(CvFinacierCommandDetail::class, 'contributions_id');
    }
    public function originResource() {
        return $this->hasOne(CvDetailOriginResource::class, 'contribution_id');
    }

    public function budgetTaskOpen(){
        return $this->hasMany(CvTaskOpenBudget::class,'associated_contributions_id');
    }

}
