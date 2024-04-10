<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\CvFile;
use App\CvPotentialLetterIntention;
use App\CvPotentialPropertyPoll;
use App\CvPotentialPropertyByFile;
use App\CvComment;
use App\CvPropertyByUser;
use App\CvPotentialPropertyQuoteByUser;
use App\CvPotentialSubType;

class CvPotentialProperty extends Model {

    protected $table = "cv_potential_properties";

    public function potentialPropertyByUser() {
        return $this->belongsToMany(User::class, "cv_potential_property_by_user", "property_id", "user_id");
    }

    public function potentialPropertyByUserActual() {
        return $this->hasOne(CvPropertyByUser::class, "property_id");
    }

    //--- Usuario que registro la carta de intencion ---//

    public function potentialPropertyByUserCreate() {
        return $this->hasOne(CvPotentialPropertyQuoteByUser::class, "property_id");
    }

    public function potentailPropertyByFile() {
        return $this->belongsToMany(CvFile::class, "cv_potential_property_by_files", "potential_property_id", "file_id");
    }

    public function potentailPropertyByFilePivot() {
        return $this->hasMany(CvPotentialPropertyByFile::class, "potential_property_id");
    }

    public function potentialLetterIntention() {
        return $this->hasOne(CvPotentialLetterIntention::class, "potential_property_id");
    }

    public function potentialPropertyPoll() {
        return $this->hasOne(CvPotentialPropertyPoll::class, "potential_property_id");
    }

    public function potentialPropertyByComment() {
        return $this->belongsToMany(CvComment::class, "cv_potential_by_comment", "potential_id", "comment_id");
    }

    public function potentialPropertySubType() {
        return $this->belongsTo(CvPotentialSubType::class, "potential_sub_type_id");
    }

    public function process(){
        return $this->hasOne(CvProcess::class, 'potential_property_id', 'id');
    }
    public function processMany(){
        return $this->hasMany(CvProcess::class, 'potential_property_id', 'id');
    }

}
