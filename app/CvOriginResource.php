<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvDetailOriginResource;
use App\CvBudget;

class CvOriginResource extends Model
{
    public function detailOriginResource() {
        return $this->hasMany(CvDetailOriginResource::class, 'origin_id');
    }
    public function budgetOriginResource() {
        return $this->belongsTo(CvBudget::class, 'budget_id');
    }
}
