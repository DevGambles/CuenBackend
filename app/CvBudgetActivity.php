<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudgetPrice;

class CvBudgetActivity extends Model
{
    protected $table = "cv_budget_activity";
    
    public function budgetPrice(){
        return $this->belongsTo(CvBudgetPrice::class, '');
    }
}
