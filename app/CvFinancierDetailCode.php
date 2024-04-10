<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvFinancierAction;
use App\CvFinacierCommandDetail;

class CvFinancierDetailCode extends Model
{
    public function actionFinancier() {
        return $this->belongsTo(CvFinancierAction::class, "actiion_id");
    }

    public function detailCommandFinancier() {
        return $this->hasMany(CvFinacierCommandDetail::class, "financier_detail_id");
    }
}
