<?php

use Illuminate\Database\Seeder;
use App\CvEntitiesPermission;
use App\CvEntities;
use App\CvPermission;

class CvEntitiesPermissionSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        // --- Obtener el total de las entidades y permisos --- //
        $totalEntities = CvEntities::count();
        $totalPermission = CvPermission::count();


        for ($x = 1; $x <= $totalEntities; $x++) {
            for ($y = 1; $y <= $totalPermission; $y++) {

                $entities_permission = [
                    [
                        'permission_id' => $y,
                        'entities_id' => $x,
                        'created_at' => date('Y-m-d H:m:s'),
                        'updated_at' => date('Y-m-d H:m:s')
                    ]
                ];

                CvEntitiesPermission::insert($entities_permission);
            }
        }
    }

}
