<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvMonitoringFile;
use App\CvMonitoringPoint;

class CvMonitoringPoint extends Model {

    protected $table = "cv_monitoring_points";

    public function point() {
        return $this->belongsTo(CvMonitoringPoint::class, 'monitoring_point_id', 'id');
    }

    public function pointFilesMonitoring() {
        return $this->hasMany(CvMonitoringFile::class, 'monitoring_point_id', 'id');
    }

    public function pointCommentsMonitoring() {
        return $this->hasMany(CvMonitoringPoint::class, 'monitoring_point_id');
    }

}
