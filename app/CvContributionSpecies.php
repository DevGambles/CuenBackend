<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvContributionSpecies extends Model
{
    public function contributions() {
        return $this->belongsTo(CvAssociatedContribution::class, 'contributions_id');
    }
}
