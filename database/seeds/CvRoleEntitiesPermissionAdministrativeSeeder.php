<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvRoleEntitiesPermissionAdministrativeSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol administrativo *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if (
                    $x == 13 || $x == 14 || $x == 15 || $x == 16 ||
                    $x == 17 || $x == 18 || $x == 19 || $x == 20 ||
                    $x == 21 || $x == 37 || $x == 57 || $x == 69 ||
                    $x == 70 || $x == 71 || $x == 72
            ) {

                $rol_entities_permission = [
                    [
                        'role_id' => 2,
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
