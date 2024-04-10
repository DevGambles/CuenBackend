<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CvTypeContractBolsa extends Model
{
    public function files(){
        return $this->hasMany(CvTypeFileContract::class, 'type_contract_bolsa');
    }
}
