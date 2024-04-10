<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvIncome extends Model
{
    protected $fillable = [
        'name',
        'value',
        'code',
        'valueOrigin'
    ];
}
