<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvPoolProcess;
use App\User;

class CvPoolActionByUser extends Model {

    protected $table = "cv_pool_actions_by_user_contractor";
    public $timestamps = false;

    public function poolProcess() {
        return $this->belongsTo(CvPoolProcess::class, "pool_by_process_id");
    }

    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
    public function taskExecutionByUser(){
        return $this->hasMany(CvTaskExecutionUser::class, 'pool_contractor_id');
    }

}
