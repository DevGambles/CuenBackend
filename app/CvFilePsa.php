<?php

namespace App;
use App\User;

use Illuminate\Database\Eloquent\Model;

class CvFilePsa extends Model
{
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
