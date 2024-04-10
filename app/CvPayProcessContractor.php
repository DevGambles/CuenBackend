<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvPayProcessContractor extends Model {

    protected $table = "cv_pay_process_contractor";

    public function files() {
        return $this->belongsToMany(CvFile::class, 'cv_file_by_pay_process_contractor', 'pay_id', 'file_id');
    }
    
    public function comment() {
        return $this->belongsToMany(CvComment::class, 'cv_comment_by_pay_process_contractor', 'pay_id', 'comment_id');
    }

}
