<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;
use App\CvContributionPerShare;

class CvAssociated extends Model {

    protected $table = "cv_associateds";
    protected $fillable = [
        'name',
        'code',
        'nit',
        'type',
    ];

    public function associatedContribution() {
        return $this->hasMany(CvAssociatedContribution::class, 'associeted_id');
    }

    public function contributions() {
        return $this->belongsTo(CvContributionPerShare::class, 'associated_id', 'id');
    }

    public function getContibutions(){
        return $this->hasMany(CvAssociatedContribution::class, 'associated_id', 'id');
    }

}
