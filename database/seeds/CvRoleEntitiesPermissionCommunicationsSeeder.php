<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvRoleEntitiesPermissionCommunicationsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol de comunicación *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if ($x == 17 || $x == 37 || $x == 57 || $x == 58 || $x == 59 || $x == 60 || $x == 21 || $x == 22 || $x == 23 ||
                $x == 24 || $x == 69 || $x == 70 || $x == 71 || $x == 72 || $x == 13 || $x == 14 || $x == 15 || $x == 16 ||
                $x == 53 || $x == 49 || $x == 50) {

                $rol_entities_permission = [
                    [
                        'role_id' => 13,
                        'entities_permission_id' => $x,
                        'created_at' => date('Y-m-d H:m:s'),
                        'updated_at' => date('Y-m-d H:m:s')
                    ], [
                        'role_id' => 17,
                        'entities_permission_id' => $x,
                        'created_at' => date('Y-m-d H:m:s'),
                        'updated_at' => date('Y-m-d H:m:s')
                    ]
                ];

                CvRoleEntitiesPermission::insert($rol_entities_permission);
            }
        }
    }

}
