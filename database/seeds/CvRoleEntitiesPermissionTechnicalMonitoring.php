<?php

use App\CvEntitiesPermission;
use App\CvRoleEntitiesPermission;
use Illuminate\Database\Seeder;

class CvRoleEntitiesPermissionTechnicalMonitoring extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // *** Consultar el total de las entidades con los permisos *** //
        $entitiesPermission = CvEntitiesPermission::count();

        // *** Permisos para rol de Tecnico de monitoreo *** //

        for ($x = 1; $x <= $entitiesPermission; $x++) {

            if ($x == 17 || $x == 18 || $x == 19 || $x == 20 || $x == 37 || $x == 13 || $x == 21) {

                $rol_entities_permission = [
                    [
                        'role_id' => 14,
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
