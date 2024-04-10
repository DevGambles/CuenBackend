<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvTaskOpenBudget extends Model
{
    protected $table = 'cv_task_open_budgets';

    public function associateContribution(){
        return $this->belongsTo(CvAssociatedContribution::class,'associated_contributions_id');
    }
}
