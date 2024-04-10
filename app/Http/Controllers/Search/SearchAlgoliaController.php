<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\CvSearchCuencaAlgolia;

class SearchAlgoliaController extends Controller {

    //*** Registro para el buscador general con la conexion en algolia ***//

    /*
     * $data => Información relevante para el buscardor
     * $identity => Identificador para saber si es un registro o actualizacion
     */

    public function registerSearchUniversal($data) {

        $existSearch = CvSearchCuencaAlgolia::where("entity_id", $data["entity_id"])->where("type", $data["type"])->first();

        if (!empty($existSearch)) {
            $newRegistreSearch = CvSearchCuencaAlgolia::find($existSearch->id);
        } else {
            $newRegistreSearch = new CvSearchCuencaAlgolia();
        }

        $newRegistreSearch->name = $data["name"];
        $newRegistreSearch->description = $data["description"];
        $newRegistreSearch->description_short = utf8_encode(substr($data["description"], 0, 350) . "...") ;
        $newRegistreSearch->type = $data["type"];
        $newRegistreSearch->entity_id = $data["entity_id"];
        if ($newRegistreSearch->name == null){
            $newRegistreSearch->name = "PSA";
        }

        if ($newRegistreSearch->save()) {
            return 200;
        } else {
            return 500;
        }
    }

}
