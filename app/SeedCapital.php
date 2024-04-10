<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvComment;

class SeedCapital extends Model {

    public function associated() {
        return $this->belongsTo(CvAssociated::class, 'cv_associateds_id', 'id');
    }

    public function commentOne() {
        return $this->hasOne('App\CvCommentBySeedCapital');
    }

    public function commentMany() {
        return $this->belongsToMany(CvComment::class, "cv_comment_by_seed_capitals", 'seed_capital_id', 'comment_id');
    }

}
