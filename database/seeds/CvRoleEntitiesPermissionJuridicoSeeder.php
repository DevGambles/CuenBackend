<?php

use Illuminate\Database\Seeder;
use App\CvRoleEntitiesPermission;
use App\CvEntitiesPermission;

class CvRoleEntitiesPermissionJuridicoSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol de juridico *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if (
                    $x == 9  || $x == 10 || $x == 11 || $x == 12 ||
                    $x == 13 || $x == 14 || $x == 15 || $x == 16 ||
                    $x == 17 || $x == 18 || $x == 19 || $x == 20 ||
                    $x == 21 || $x == 25 || $x == 26 || $x == 27 ||
                    $x == 37 || $x == 69 || $x == 70 || $x == 71 ||
                    $x == 72 || $x == 49 || $x == 50 || $x == 51 ||
                    $x == 52
            ) {

                $rol_entities_permission = [
                    [
                        'role_id' => 8,
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
