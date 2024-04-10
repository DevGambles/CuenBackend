<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvActions;

class CvTariffActionContractor extends Model
{
    public function actionTable() {
        return $this->belongsTo(CvActions::class, "action_id");
    }

    public function materialTable() {
        return $this->belongsTo(CvBudgetPriceMaterial::class, "budget_prices_material_id");
    }
}
