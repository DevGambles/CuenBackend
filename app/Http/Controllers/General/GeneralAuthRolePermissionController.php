<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;
use App\CvEntities;

class GeneralAuthRolePermissionController extends Controller {

    // *** Consultar entidades y permisos de acuerdo al rol autenticado *** //

    public function authRoleEntityPermission() {

        $rolesEntitiesPermission = CvRoleEntitiesPermission::where("role_id", $this->userLoggedInRol())->get();

        $entitiesPermissionRoles = array();

        foreach ($rolesEntitiesPermission as $roleEntityPermission) {

            CvEntitiesPermission::find($roleEntityPermission->entities_permission_id)->permission->id;

            // --- entidades --- //
            array_push($entitiesPermissionRoles, array(
                "entity" => CvEntitiesPermission::find($roleEntityPermission->entities_permission_id)->entities->id,
                "role" => intval($this->userLoggedInRol()),
                "permission" => CvEntitiesPermission::find($roleEntityPermission->entities_permission_id)->permission->id
                    )
            );
        }

        return $entitiesPermissionRoles;
    }

}
