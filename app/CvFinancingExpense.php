<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvFinancingExpense extends Model
{
    protected $fillable = [
        'name',
        'codeCenter',
        'value',
        'balance',
        'valueOrigin'
    ];
}
