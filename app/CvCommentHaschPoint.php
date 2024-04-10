<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvTask;
use App\CvTaskOpen;
use App\CvTaskExecution;


class CvCommentHaschPoint extends Model
{
    public function taskReal(){
        return $this->belongsTo(CvTask::class, 'task_id');
    }
    public function taskOpen(){
        return $this->belongsTo(CvTaskOpen::class, 'task_id');
    }
    public function taskExecution(){
        return $this->belongsTo(CvTaskExecution::class, 'task_id');
    }
}
