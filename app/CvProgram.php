<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProject;

class CvProgram extends Model {

    protected $table = "cv_program";

    //Consult type of project

    public static function consultCvProgram() {

        return CvProgram::all();
    }

    //Relaciones
    public function project() {
        return $this->belongsToMany(CvProject::class, 'cv_program_by_project', 'program_id', 'project_id');
    }

    public function programByProject() {
        return $this->belongsToMany(CvProject::class, 'cv_program_by_project', 'program_id', 'id');
    }

}
