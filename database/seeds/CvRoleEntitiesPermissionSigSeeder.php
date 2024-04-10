<?php

use Illuminate\Database\Seeder;
use App\CvEntitiesPermission;
use App\CvRoleEntitiesPermission;

class CvRoleEntitiesPermissionSigSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol de sig ** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if ($x == 13 || $x == 14 || $x == 15 || $x == 16 || $x == 17 || $x == 18 || $x == 19 || $x == 20 || $x == 21 || $x == 25 || $x == 26 || $x == 27 || $x == 28 || $x == 37 || $x == 61 || $x == 62 || $x == 63 || $x == 64 || $x == 69) {

                $rol_entities_permission = [
                    [
                        'role_id' => 6,
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
