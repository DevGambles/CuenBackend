<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;
use Auth;

class GeneralRoleEntityPermissionController extends Controller {

    //Consultar los permisos y entidades de un rol

    public function consultEntityPermision() {

        try {
            $listEntitiesPermission = array();

            $entitiesPermission = CvEntitiesPermission::get();

            foreach ($entitiesPermission as $entityPermision) {

                array_push($listEntitiesPermission, array(
                    "id" => $entityPermision->id,
                    "entities" => $entityPermision->entities,
                    "permission" => $entityPermision->permission
                ));
            }

            return $listEntitiesPermission;
        } catch (Exception $e) {
            return "Se ha presentado un error: " . $e->getMessage() . "\n";
        }
    }

}
