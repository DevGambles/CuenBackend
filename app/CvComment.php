<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class CvComment extends Model {

    protected $table = "cv_comment";

    // *** Relaciones *** //
    public function task() {
        return $this->belongsToMany(CvTask::class, 'cv_comment_by_task', 'task_id', 'comment_id');
    }

    public function user() {
        return $this->belongsToMany(User::class, 'cv_comment_by_task', 'user_id', 'comment_id');
    }
    
}
