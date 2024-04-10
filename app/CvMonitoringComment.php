<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvMonitoringByFlow;

class CvMonitoringComment extends Model {

    protected $table = "cv_monitoring_comment";
    
      public function monitoringbyflow() {
        return $this->belongsTo(CvMonitoringByFlow::class, 'monitoring_comment_id', 'id');
    }

}
