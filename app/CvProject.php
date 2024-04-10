<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProgram;
use App\CvTask;
use App\CvProjectActivity;

class CvProject extends Model {

    protected $table = "cv_project";

    protected $hidden = ['pivot'];

    //*** Consulta de relaciones entre tablas ***//

    public function task() {
        return $this->belongsToMany(CvTask::class, 'cv_task_by_process', 'project_id', 'task_id');
    }

    public function typeProgram() {
        return $this->belongsToMany(CvProgram::class, 'cv_program_by_project', 'project_id', 'program_id');
    }

    public function projectActities() {
        return $this->belongsToMany(CvProjectActivity::class, 'cv_project_by_activity', 'project_id', 'activity_id');
    }
    
    public function programByProject(){
        return $this->belongsToMany(CvProgram::class, 'cv_program_by_project', 'project_id', 'program_id');
    }

}
