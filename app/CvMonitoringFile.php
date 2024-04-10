<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvMonitoringFile extends Model
{
     protected $table = "cv_monitoring_files";
     
      public function file() {
        return $this->belongsTo(CvMonitoringFile::class, 'monitoring_file_id', 'id');
    }

}
