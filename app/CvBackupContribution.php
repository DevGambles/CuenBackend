<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvAssociatedContribution;

class CvBackupContribution extends Model
{
     protected $table = "cv_backup_contributions";
     
     public function logContribution() {
        return $this->belongsTo(CvAssociatedContribution::class, 'contribution_id');
    }
}
