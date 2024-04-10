<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvEntitiesPermissionAdministratorSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Todos los permisos para el rol de super administrador *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            $rol_entities_permission = [
                [
                    'role_id' => 1,
                    'entities_permission_id' => $x,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                ],
            ];

            CvRoleEntitiesPermission::insert($rol_entities_permission);
        }
    }

}
