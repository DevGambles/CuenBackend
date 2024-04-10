<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvContract extends Model
{
    public function typeContract(){
        return $this->belongsTo(CvTypeContractBolsa::class, 'type_contract_bolsa_id');
    }

    public function typeFile(){
        return $this->belongsTo(CvTypeFileContract::class, 'type_file_contract');
    }
}
