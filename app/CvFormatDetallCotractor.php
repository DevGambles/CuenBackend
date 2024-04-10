<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvFormatSowingCotractor;

class CvFormatDetallCotractor extends Model {

    public function sowingByFormat() {

        return $this->belongsToMany(CvFormatSowingCotractor::class, 'cv_format_by_sowings', 'detall_contractor_id', 'detall_sowing_id');
    }

}
