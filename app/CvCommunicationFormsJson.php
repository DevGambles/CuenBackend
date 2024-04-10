<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvFilesFormComunication;

class CvCommunicationFormsJson extends Model
{
    public function getFileCommunication(){
        return $this->hasMany(CvFilesFormComunication::class,'formsjson_id');
    }

    public function getTaskOpen(){
        return $this->belongsTo(CvTaskOpen::class, 'task_id');
    }
}
