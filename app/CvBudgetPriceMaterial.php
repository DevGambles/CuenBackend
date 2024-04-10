<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvUnits;

class CvBudgetPriceMaterial extends Model {

    protected $table = "cv_budget_price_material";

    public function units() {
        return $this->belongsTo(CvUnits::class, 'unit_id','id');
    }

}
