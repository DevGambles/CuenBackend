<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvMonitoringComment;
use App\CvProcess;
use App\CvTypeMonitoring;
use App\User;
use App\CvMonitoringPoint;
use App\CvMonitoringByFlow;
use App\CvFormMonitoring;

class CvMonitoring extends Model {

    protected $table = "cv_monitoring";

    //*** Relaciones ***//

    public function monitoringByFlow() {

        return $this->hasMany(CvMonitoringByFlow::class, "monitoring_id");
    }

    public function commentByMonitoring() {

        return $this->hasMany(CvMonitoringComment::class, "monitoring_id");
    }

    public function commentByPoint() {

        return $this->hasMany(CvMonitoringComment::class, "monitoring_point_id");
    }

    public function formByMonitoring() {

        return $this->hasMany(CvFormMonitoring::class, "monitoring_id");
    }

    public function userByMonitoring() {

        return $this->belongsTo(User::class, "user_id");
    }

    public function userByMonitoringOne() {

        return $this->belongsTo(User::class, "user_id");
    }

    public function pointByMonitoring() {

        return $this->hasMany(CvMonitoringPoint::class, "monitoring_id");
    }

    public function process() {

        return $this->belongsTo(CvProcess::class, "process_id");
    }

    public function typeMonitoring() {

        return $this->belongsTo(CvTypeMonitoring::class, "type_monitoring_id");
    }

}
