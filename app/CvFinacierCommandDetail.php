<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvFinacierCommandDetail extends Model
{
    public function contributionAssociated() {
        return $this->belongsTo(CvAssociatedContribution::class, 'contributions_id');
    }
    public function associate() {
        return $this->belongsTo(CvAssociated::class, 'associated_id');
    }
}
