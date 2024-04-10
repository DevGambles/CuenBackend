<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvGoalsForContribution extends Model {

    public function Contribution() {
        return $this->belongsTo(CvAssociatedContribution::class, 'contributions_id', 'id');
    }

}
