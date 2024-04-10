<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvBudgetByBudgetExcution;
use App\CvTariffActionContractor;

class CvBudgetByBudgetContractor extends Model
{
    public function budgetExecution() {
        return $this->hasOne(CvBudgetByBudgetExcution::class, "budget_contractor_id");
    }
    public function tariffAction() {
        return $this->belongsTo(CvTariffActionContractor::class, "tariff_id");
    }

    public  function excecution(){
        return $this->hasMany(CvBudgetByBudgetExcution::class,'budget_contractor_id');
    }
}
