<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvRoleEntitiesPermissionHidricSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol de recuerso hidrico *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if ($x > 1) {

                $rol_entities_permission = [
                    [
                        'role_id' => 10,
                        'entities_permission_id' => $x,
                        'created_at' => date('Y-m-d H:m:s'),
                        'updated_at' => date('Y-m-d H:m:s')
                    ],
                    [
                        'role_id' => 16,
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
