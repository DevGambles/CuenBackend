<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvDetailOriginResource extends Model
{
    public function associatedContribution() {
        return $this->belongsTo(CvAssociatedContribution::class, 'contribution_id');
    }
}
