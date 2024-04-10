<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProcess;

class CvUnionOfProcess extends Model
{
    public function processSons() {
        return $this->belongsTo(CvProcess::class, 'process_son_id');
    }
}
