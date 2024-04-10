<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvRoleEntitiesPermissionCoordinationGuardSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol coordinador *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if ($x > 1) {

                $rol_entities_permission = [
                    [
                        'role_id' => 3,
                        'entities_permission_id' => $x,
                        'created_at' => date('Y-m-d H:m:s'),
                        'updated_at' => date('Y-m-d H:m:s')
                    ],
                ];

                CvRoleEntitiesPermission::insert($rol_entities_permission);
            }
        }
    }

}
