<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProcess;
use App\User;

class CvLetterIntention extends Model {

    protected $table = "cv_letter_intention";

    public function process() {
        return $this->belongsTo(CvProcess::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function taskType() {

        return $this->belongsTo(CvTaskType::class, 'type_id');
    }

    public function files() {
        return $this->belongsToMany(CvFile::class, 'cv_file_by_letter_intention', 'letter_intention_id', 'file_id');
    }

}
