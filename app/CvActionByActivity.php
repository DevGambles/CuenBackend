<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProjectActivity;

class CvActionByActivity extends Model {

    protected $table = "cv_actions_by_activity";
    public $timestamps = false;

    public function Activiteadd() {
        return $this->belongsTo(CvProjectActivity::class,'activity_id');
    }

}
