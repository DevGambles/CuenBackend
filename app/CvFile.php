<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\CvTaskByFile;

class CvFile extends Model {

    protected $table = "cv_files";

    public function users() {
        return $this->belongsToMany(User::class, 'cv_users_files', 'cv_file_id', 'user_id');
    }
    
     public function taskDetall() {
        return $this->hasOne(CvTaskByFile::class, 'file_id');
    }

}
