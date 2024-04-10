<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        //*** Rutas automaticas ***//
        $this->call(CvRoutesAutomaticsSeeder::class);

        //*** Datos para contratista ***//
        $this->call(CvContractorModalitiesTableSeeder::class);
        $this->call(CvTypeContractsTableSeeder::class);
        $this->call(CvTypeContractBolsaSeeder::class);
        $this->call(CvTypeFileContractSeeder::class);

        //*** Permisos ***//
        $this->call(CvEntitiesSeeder::class);
        $this->call(CvPermissionSeeder::class);
        $this->call(CvRoleTableSeeder::class);
        $this->call(CvEntitiesPermissionSeeder::class);
        $this->call(CvEntitiesPermissionAdministratorSeeder::class);
        $this->call(CvRoleEntitiesPermissionAdministrativeSeeder::class);
        $this->call(CvRoleEntitiesPermissionCoordinationGuardSeeder::class);
        $this->call(CvRoleEntitiesPermissionGuardSeeder::class);
        $this->call(CvRoleEntitiesPermissionSigSeeder::class);
        $this->call(CvRoleEntitiesPermissionTeamTracingSeeder::class);
        $this->call(CvRoleEntitiesPermissionJuridicoSeeder::class);
        $this->call(CvRoleEntitiesPermissionRestorationSeeder::class);
        $this->call(CvRoleEntitiesPermissionHidricSeeder::class);
        $this->call(CvRoleEntitiesPermissionFinancialSeeder::class);
        $this->call(CvRoleEntitiesPermissionAddressSeeder::class);
        $this->call(CvRoleEntitiesPermissionCommunicationsSeeder::class);
        $this->call(CvRoleEntitiesPermissionContractor::class);
        $this->call(CvRoleEntitiesPermissionTechnicalMonitoring::class);

        //*** sub tipos de tareas ***//
        $this->call(CvSubTypeTaskSeeder::class);
        $this->call(CvSubTypeTaskOpenSeeder::class);

        //*** sub tipos de predios potenciales ***//
        $this->call(CvPotentialSubTypeTableSeeder::class);

        //*** Información por defecto ***//
        $this->call(CvTaskStatusTableSeeder::class);
        $this->call(CvProgramTableSeeder::class);
        $this->call(CvProjectsDefaultTableSeeder::class);
        $this->call(CvProgramByProjectTableSeeder::class);
        $this->call(CvProjectActivitiesTableSeeder::class);
        $this->call(CvProjectByActivityTableSeeder::class);
        $this->call(CvTaskTypeTableSeeder::class);
        $this->call(CvPropertyCorrelationTableSeeder::class);

        //*** Acciones ***//
        $this->call(CvUnitsTableSeeder::class);
        $this->call(CvActionsTableSeeder::class);
        $this->call(CvBudgetPriceMaterialTableSeeder::class);
        $this->call(CvBudgetActionMaterialSeeder::class);
        $this->call(CvActionTypeTableSeeder::class);
        $this->call(CvActionByTypeTableSeeder::class);

        //*** Tipo de archivos ***//
        $this->call(CvTypeFilesSeeder::class);

        //*** Tipo de monitoreos ***//
        $this->call(CvTypeMonitoringSeeder::class);

        //***Categorias***//
        $this->call(CvCategoriesTableSeeder::class);

        //***Tipos de pqrs ***//
        $this->call(CvRolePqrsSeeder::class);
        $this->call(CvTypePqrsSeeder::class);

        //*** Financiero ***//
        $this->call(CvFinancierActionSeeder::class);
        $this->call(CvFinancierDetailCodeSeeder::class);

        //*** Actividades por tipo de tarea ***//
        $this->call(CvTaskTypeByActivitySeeder::class);

        //*** Ciudades y departamentos***//
        $this->call(CvDepartamentsTableSeeder::class);
        $this->call(CvMunicipalityTableSeeder::class);

        //*** Informacion temporal ***//
        $this->call(CvUserDefaultTableSeeder::class);
        $this->call(CvUserQuoteTableSeeder::class);

        //***Comando y control ***//
        $this->call(CvAssociatedTableSeeder::class);

        //*** Acciones por actividad ***//
        $this->call(CvActionsByActivitySeeder::class);

        //*** Variables administrativas de porcentaje sobre el presupuesto  ***/
        $this->call(CvAdminPorcentBudgetTableSeeder::class);

    }
}
