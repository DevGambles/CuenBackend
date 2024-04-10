<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvContractor;

class CvTypeContract extends Model {

    protected $table = "cv_type_contracts";

    public function contractor() {
        return $this->hasOne(CvContractor::class, 'type_contract_id');
    }

}
