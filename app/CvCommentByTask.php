<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvComment;
use App\CvTask;
use App\User;
use App\CvSubTypeTask;

class CvCommentByTask extends Model {

    protected $table = "cv_comment_by_task";
    public $timestamps = false;

    public function comment() {

        return $this->belongsTo(CvComment::class);
    }

    public function task() {

        return $this->belongsTo(CvTask::class);
    }

    public function user() {

        return $this->belongsTo(User::class);
    }

    public function subType() {

        return $this->belongsTo(CvSubTypeTask::class);
    }

}
