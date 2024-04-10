<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvContractorModality;
use App\CvTypeContract;

class CvContractor extends Model {

    protected $table = "cv_contractors";

    public function contractormodality() {
        return $this->belongsTo(CvContractorModality::class);
    }

    public function typecontract() {
        return $this->belongsTo(CvTypeContract::class);
    }

}
