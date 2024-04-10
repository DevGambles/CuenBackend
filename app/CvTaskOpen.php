<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvFileOpen;
use App\CvCommunicationFormsJson;
use App\CvFilePsa;
use App\CvTaskOpenBudget;

class CvTaskOpen extends Model {

    protected $table = "cv_task_open";

    public function process() {
        return $this->belongsTo(CvProcess::class);
    }

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subtypes(){
        return $this->belongsTo(CvTaskOpenSubType::class,'task_open_sub_type_id');
    }

    public function openFiles(){
        return $this->hasMany(CvFileOpen::class,'task_open_id')->where('state_delete', '!=', 1);
    }

    public function getFormCommunication($type){
        return $this->hasMany(CvCommunicationFormsJson::class,'task_id')->where('type', $type);
    }

    public function getFilePSA(){
        return $this->hasMany(CvFilePsa::class,'task_open_id');
    }

    public function status(){
        return $this->belongsTo(CvTaskStatus::class,'task_status_id');
    }

    public function taskOpenBudget(){
        return $this->hasOne(CvTaskOpenBudget::class,'task_open_id');
    }

    public function taskOpenBudgetMany(){
        return $this->hasMany(CvTaskOpenBudget::class,'task_open_id');
    }
}
