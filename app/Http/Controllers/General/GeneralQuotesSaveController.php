<?php

namespace App\Http\Controllers\General;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\CvPotentialPropertyQuoteByUser;
use App\CvQuota;

class GeneralQuotesSaveController extends Controller {

    //*** Activar el predio potencial del guarda cuenca ***//

    public function activePropertyPotential($propertyId) {

        $propertyByUser = CvPotentialPropertyQuoteByUser::where("property_id", $propertyId)->first();
        $propertyByUser->state = true;
        $propertyByUser->save();
    }

    //*** Predios aprobados sobre cuota total del guarda cuenca ***//

    public function consultQuotes() {
        
        $propertyByUserTotal = CvPotentialPropertyQuoteByUser::where("user_id", $this->userLoggedInId())
                        ->where("state", true)->count();

        $quote = CvQuota::where("user_id", $this->userLoggedInId())->first();

        if ($propertyByUserTotal == 0) {
            return [
                "message" => "No hay predios potenciales relacionados al usuario",
                "code" => 500
            ];
        }

        if (empty($quote)) {
            return [
                "message" => "El usuario no se le ha asignado una cuota",
                "code" => 500
            ];
        }

        $percentage = round(((double) $propertyByUserTotal / (double) $quote->quota), 2);

        return [
            "quote" => $propertyByUserTotal,
            "total" => (int) $quote->quota,
            "percentage" => ($percentage <= 1) ? $percentage : 1
        ];
    }

    //*** Validar cuota de guarda cuenca con el total de predios aprobados ***//

    public function consultQuotaTotalApproved($propertyId, $userId) {

        $propertyByUser = new CvPotentialPropertyQuoteByUser();

        $propertyByUser->property_id = $propertyId;
        $propertyByUser->user_id = $userId;

        if ($propertyByUser->save()) {
            return 200;
        }
    }

}
