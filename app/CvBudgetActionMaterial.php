<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudgetPriceMaterial;
use App\CvActions;

class CvBudgetActionMaterial extends Model {

    protected $table = "cv_budget_action_material";
    public $timestamps = false;

    public function budgetPriceMaterial() {
        return $this->belongsTo(CvBudgetPriceMaterial::class, 'budget_prices_material_id');
    }

    public function action() {
        return $this->belongsTo(CvActions::class, 'action_id');
    }

    public function actionOne() {
        return $this->belongsTo(CvActions::class, 'action_id');
    }

}
