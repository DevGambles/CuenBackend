<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvPropertyCorrelation;
use App\CvSketchProperty;

class CvProperty extends Model {

    protected $table = "cv_property";

    //*** Relaciones ***//
    public function task() {
        return $this->hasMany(CvTask::class, 'property_id');
    }
    
    public function taskOne() {
        return $this->belongsTo(CvTask::class, 'id', 'property_id');
    }

    public function contactsProperty() {
        return $this->hasMany(CvContactProperty::class, 'property_id');
    }

    public function properyCorrelation() {
        return $this->belongsTo(CvPropertyCorrelation::class, 'property_correlation_id', 'id');
    }

    public function properySketch() {
        return $this->hasOne(CvSketchProperty::class, 'property_id', 'id');
    }

}
