<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvPermission extends Model {

    protected $table = "cv_permission";

    public function getAllPermission() {

        return CvPermission::all();
    }

}
