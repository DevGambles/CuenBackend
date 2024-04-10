<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class CvCategory extends Model {
 
    public function categoryByContractor() {
        return $this->belongsToMany(User::class, 'cv_category_by_contractors', 'categories_id', 'users_id');
    }

}
