<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvComment;
use App\User;
class CvCommentByOtherTask extends Model
{
    public function comment(){
        return $this->belongsTo(CvComment::class,'comment_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
