<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvTaskType;
use App\CvTaskStatus;
use App\CvTaskUser;
use App\CvFile;
use App\CvBudget;
use App\CvProcess;
use App\User;
use App\CvSubTypeTask;
use App\CvTaskByFile;
use App\CvImgBase64Task;

class CvTask extends Model {

    protected $table = "cv_task";

    //*** Consulta de relaciones entre tablas ***//

    public function taskUser() {

        return $this->hasMany(CvTaskUser::class, "task_id");
    }

    public function taskFile() {

        return $this->belongsToMany(CvFile::class, "cv_task_by_files", 'task_id', 'file_id');
    }

    public function taskFilePivot() {

        return $this->hasMany(CvTaskByFile::class, 'task_id');
    }

    public function taskType() {

        return $this->belongsTo(CvTaskType::class);
    }

    public function taskSubType() {

        return $this->belongsTo(CvSubTypeTask::class);
    }

    public function taskStatus() {

        return $this->belongsTo(CvTaskStatus::class);
    }

    public function property() {

        return $this->belongsTo(CvProperty::class, 'property_id');
    }

    public function geoJson() {
        return $this->hasMany(CvGeoJson::class, 'task_id');
    }
    
    public function geoJsonOne() {
        return $this->hasOne(CvGeoJson::class, 'task_id');
    }

    public function user() {
        return $this->belongsToMany(User::class, 'cv_task_by_user', 'task_id');
    }

    public function comment() {
        return $this->belongsToMany(CvComment::class, 'cv_comment_by_task', 'task_id', 'comment_id', 'user_id');
    }

    public function commentBySubType() {
        return $this->belongsToMany(CvComment::class, 'cv_comment_by_task', 'task_id', 'task_sub_type_id');
    }

    public function process() {
        return $this->belongsToMany(CvProcess::class, 'cv_task_by_process', 'task_id', 'process_id');
    }

    public function budget() {
        return $this->hasMany(CvBudget::class, 'task_id');
    }

    public function taskFileBase64() {
        return $this->belongsToMany(CvImgBase64Task::class, "cv_img_base64_task_by_generals", 'task_id', 'file_id');
    }

}
