<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class CvSearchCuencaAlgolia extends Model {

    use Searchable;

    protected $table = "cv_search_cuenca_algolia";

}
