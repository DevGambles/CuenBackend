<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvContractor;

class CvContractorModality extends Model {

    protected $table = "cv_contractor_modalities";

    public function contractor() {
        return $this->hasOne(CvContractor::class, 'contract_modality_id');
    }

}
