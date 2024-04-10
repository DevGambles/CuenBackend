<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvProjectActivity;
use App\CvFinancierDetailCode;

class CvFinancierAction extends Model
{

    protected $fillable = [
        'name',
        'code'
    ];
    public function activiteFinancier() {
        return $this->belongsTo(CvProjectActivity::class, "activity_id");
    }

    public function financierDetail() {
        return $this->hasMany(CvFinancierDetailCode::class, 'actiion_id');
    }
}
