<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvFormatDetallCotractor;

class CvFormatBySowing extends Model {

    public function detallContractor() {
        return $this->belongsTo(CvFormatDetallCotractor::class,  'detall_contractor_id');
    }

}
