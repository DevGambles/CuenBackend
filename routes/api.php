<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

use App\Http\Controllers\General\GeneralEmailController;
use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group whichconsultMapTask
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */


/*
 * Rutas que no necesitan validacion de permisos en la base de datos y de autenticacion en la plataforma
 */

//*** Roles o dependencias ***//
Route::get('pqrs/dependencie', 'General\GeneralPqrsController@consultDependencies');

//*** PQRS ***//
Route::post('pqrs', 'General\GeneralPqrsController@registerPQRS');
Route::get('type/pqrs', 'General\GeneralPqrsController@consultTypePQRS');

//*** Control de roles y autenticacion ***//
Route::group(['middleware' => ['role_entities_permission', 'auth']], function () {

    //*** Super Administrator Services ***//
    Route::get('generals/admin/user', 'Admin\UserController@allUsers');
    Route::get('generals/admin/rol', 'Admin\UserController@allRole');
    Route::get('generals/admin/rolCordinator', 'Admin\UserController@coordinatorRole');
    Route::get('generals/admin/user/{id_user}', 'Admin\UserController@detailUser');
    Route::post('generals/admin/user', 'Admin\UserController@createUser');
    Route::put('generals/admin/user', 'Admin\UserController@updateUser');

    Route::get('generals/admin/category', 'Admin\CategoryController@allCategory');
    Route::get('generals/admin/category/{id_category}', 'Admin\CategoryController@detailCategory');
    Route::post('generals/admin/category', 'Admin\CategoryController@createCategory');
    Route::put('generals/admin/category', 'Admin\CategoryController@updateCategory');

    Route::get('generals/admin/program', 'Admin\ProgramsController@allProgram');
    Route::get('generals/admin/program/{id_program}', 'Admin\ProgramsController@detailProgram');
    Route::post('generals/admin/program', 'Admin\ProgramsController@createProgram');
    Route::put('generals/admin/program', 'Admin\ProgramsController@updateProgram');

    Route::get('generals/admin/project', 'Admin\ProjectController@allProject');
    Route::get('generals/admin/project/{id_project}', 'Admin\ProjectController@detailProject');
    Route::post('generals/admin/project', 'Admin\ProjectController@createProject');
    Route::put('generals/admin/project', 'Admin\ProjectController@updateProject');

    Route::get('generals/admin/activities', 'Admin\ActivitieController@allActivitie');
    Route::get('generals/admin/activities/{id_activities}', 'Admin\ActivitieController@detailActivitie');
    Route::post('generals/admin/activities', 'Admin\ActivitieController@createActivitie');
    Route::put('generals/admin/activities', 'Admin\ActivitieController@updateActivitie');

    Route::get('generals/admin/types', 'Admin\ActionsController@allTypesActions');
    Route::get('generals/admin/action', 'Admin\ActionsController@allActions');
    Route::get('generals/admin/action/{id_actions}', 'Admin\ActionsController@detailActions');
    Route::post('generals/admin/action', 'Admin\ActionsController@createActions');
    Route::put('generals/admin/action', 'Admin\ActionsController@updateActions');

    Route::get('generals/admin/financierAction', 'Admin\ActionsFinancierController@allFinancierActions');
    Route::get('generals/admin/financierActionByActivityId/{idActivity}', 'Admin\ActionsFinancierController@getActionsByActivityId');
    Route::get('generals/admin/financierAction/{id_actions}', 'Admin\ActionsFinancierController@detailFinancierActions');
    Route::post('generals/admin/financierAction', 'Admin\ActionsFinancierController@createFinancierActions');
    Route::put('generals/admin/financierAction', 'Admin\ActionsFinancierController@updateFinancierActions');

    Route::get('generals/admin/financierCommandDetail', 'Admin\LevelDetailController@allFinancierCommandDetail');
    Route::get('generals/admin/financierCommandDetail', 'Admin\LevelDetailController@detailFinancierCommandDetail');
    Route::get('generals/admin/financierLevelDetail', 'Admin\LevelDetailController@allFinancierLevelDetail');
    Route::get('generals/admin/financierLevelDetail/{id_actions}', 'Admin\LevelDetailController@detailFinancierLevelDetail');
    Route::post('generals/admin/financierLevelDetail', 'Admin\LevelDetailController@createFinancierLevelDetail');
    Route::put('generals/admin/financierLevelDetail', 'Admin\LevelDetailController@updateFinancierLevelDetail');

    Route::get('generals/admin/processFinish', 'ProcessController@superAdminProcess');

    Route::resource('generals/associates', 'Associated\AssociatedController');


    Route::get('generals/admin/unitsMeasure', 'Admin\ActionsController@getAllUnitsMeasure');

    //*** Search ***//
    Route::get('generals/search/{search}', 'General\GeneralSearchController@searchPQRS');

    //*** IPH ***//
    Route::get('generals/IPHYear/{year}', 'Report\ReportIPHController@reportIPHYear');

    //*** PQRS ***//
    Route::get('comunication/pqrs', 'General\GeneralPqrsController@consultPQRS');
    Route::put('comunication/pqrs/{id}', 'General\GeneralPqrsController@updatePQRS');
    Route::get('comunication/pqrs/{id}', 'General\GeneralPqrsController@consultSpecificPQRS');
    Route::post('comunication/pqrs/response', 'General\GeneralPqrsController@responsePQRS');

    //*** Consultar procesos ***//
    Route::get('process/api', 'Api\ProcessController@infoProcess');
    Route::get('process/filter/taskMeasurement', 'ProcessController@taskMeasurement');

    //*** Consultar tareas ***//
    Route::get('tasks/api', 'Api\TaskController@listTasks');

    //*** Procedimiento ***//
    Route::get('generals/consult/files/process/potential/property/{process_id}', 'ProcessController@consultFilesProcessWithPotentialProperty');
    Route::get('generals/consult/all_activities/process/{id_process}/{type}', 'General\GeneralProcessController@getActivitiesProcess');

    //*** Tareas por procedimiento ***//
    Route::get('tasks/process/api/{process}', 'General\GeneralTaskController@consultTaskByProcess');
    Route::get('tasks/process/monitoring/{process}', 'ProcessController@consultTaskByProcessByMonitoring');
    Route::get('tasks/process/with/property/{process}', 'ProcessController@consultProcessWithTaskProperty');

    //*** Tareas pronto a vencer ***//
    Route::get('tasks/soon/overcome', 'General\GeneralTaskController@listsTaskByUserSoonOvercome');

    //*** GeoJson - Mapa del predio por tarea ***//
    Route::post('maps/task/geojson/api', 'General\GeneralMapController@propertyGeoJsonTask');
    //*** Medicion por cada guarda cuenca vinculado a la tarea de medicion ***//
    Route::post('maps/task/by/guard/basin/geojson', 'General\GeneralMapController@registerMapsOfGuardBasinByTask');
    Route::post('maps/task/by/equipment/basin/geojson', 'General\GeneralMapController@registerMapsSeguimentEquipmentByTask'); //esta pasa de tarea no se usa pero e sbase para la union
    //*** Unir mapa y pasar tarea ***//
    Route::post('maps/union/next/task', 'General\GeneralMapController@unimMapAndnNextTask');

    //*** Encuesta ***//
    Route::resource('maps/property', 'Api\PropertyController');
    Route::get('maps/property/consult/potential/api/{id}', 'Api\PropertyController@consultPropertySpecific');
    Route::get('generals/getotherDMinute/forTask/{id_task}', 'Api\PropertyController@getMinuteDataforTask');
    Route::get('generals/getotherDMinute/forProperty/{id_property}', 'Api\PropertyController@getMinuteDataforProperty');
    Route::post('generals/addMinute/otherData', 'Api\PropertyController@addMinuteData');

    //*** Cargar archivos ***//
    Route::post('maps/loadFiles', 'General\GeneralFileController@saveFiles');

    //*** Solicitar carta de tradicion *** //
    Route::get('generals/request/ct/{task}', 'General\GeneralTaskController@requestTraditionCertificate');

    //*** Acciones ***//
    Route::get('generals/actions/{type}', 'General\GeneralActionsController@actions');
    Route::get('generals/actions/materials/{action}', 'General\GeneralActionsController@materials');
    Route::get('generals/materials/', 'General\GeneralActionsController@allMaterials');
    Route::get('generals/actionall/formaterial', 'General\GeneralActionsController@allActionForMaterial');

    //*** Contratista ***//
    Route::get('generals/category_all', 'General\GeneralContractorsController@category_all');
    Route::get('generals/contractorCategory/{id_category}', 'General\GeneralContractorsController@contractorCategory');
    Route::get('generals/getParentProcess', 'General\GeneralContractorsController@getParentProcess');
    Route::get('generals/unionProcess', 'General\GeneralContractorsController@getunionProcess');
    Route::get('generals/budgetContractor/{id_budget}', 'General\GeneralContractorsController@getbudgetContractor');
    Route::post('generals/budgetContractor', 'General\GeneralContractorsController@budgetContractor');
    Route::post('generals/TariffActionContractor', 'General\GeneralContractorsController@insertTarifContracrtorMoneyAction');
    Route::post('generals/unionProcess', 'General\GeneralContractorsController@unionProcess');

    //*** Contrato ***//
    Route::post('contractor/contractorFormat/{id_task}', 'General\GeneralContractorsController@contractorFormat');
    Route::post('contractor/contractorSowing/{id_task}', 'General\GeneralContractorsController@contractorSowing');
    Route::post('contractor/taskOpen/nextSubtype', 'General\GeneralTaskOpenController@nextTaskSubtype');
    Route::get('contractor/detallFormatContractor/{id_pool}', 'General\GeneralContractorsController@detallFormatContractor');
    Route::get('contractor/task/open', 'General\GeneralTaskOpenController@consultTaskOpen');
    Route::get('contractor/getGeoMap/{id_task}', 'General\GeneralContractorsController@getGeoMap');

    //*** Pqrs ***//
    Route::post('generals/dependencies/roles/pqrs', 'General\GeneralPqrsController@dependenciesRolesPqrs');

    //*** Monitoreos y mantenimiento ***//
    Route::resource('monitoring', 'General\GeneralMonitoringController');
    Route::post('monitoring/comment', 'General\GeneralMonitoringController@registerCommentMonitoring');
    Route::get('monitoring/process/task/{id}', 'General\GeneralMonitoringController@monitoringByProcessTask');
    Route::post('monitoring/process', 'General\GeneralMonitoringController@monitoringProcess');
    Route::get('monitoring/process/{id}/geojson', 'General\GeneralMapController@consultProcessGeojsonWithBudget');
    Route::get('monitoring/{hash}/geojson/action/material', 'General\GeneralMapController@consultHashMaterialsActions');
    Route::get('monitoring/{id}/comment', 'General\GeneralMonitoringController@consultMonitoringComments');
    Route::get('monitoring/point/{id}/comment', 'General\GeneralMonitoringController@consultMonitoringPointsComments');
    Route::post('monitoring/files/send', 'General\GeneralMonitoringController@saveImgMonitoring');
    Route::post('monitoring/file/point/img/send', 'General\GeneralMonitoringController@saveMonitoringPointsImgAndComment');
    Route::post('monitoring/form/{monitoring}/{type}', 'General\GeneralFormMonitoringController@registerFormMonitoring');
    Route::get('monitoring/form/{monitoring}/{type}', 'General\GeneralFormMonitoringController@consultFormMonitoring');
    Route::get('monitoring/asigmentCreator/{monitoring}', 'General\GeneralMonitoringController@monitoringDevolutionCreator');
    Route::post('monitoring/provider/evaluation/form/{monitoring}', 'General\GeneralFormMonitoringController@registerFormProvider');

    /*
     * save image and comments of task execution by point.
     * */
    Route::post('generals/file/point/img/send', 'General\GeneralMonitoringController@saveMonitoringPointsImgAndComment');

    //*** Consultar formulario stard ***//
    Route::get('monitoring/form/stard/process/{task_id}', 'General\GeneralFormMonitoringController@consultInformationFormStardProcess');

    //*** Reportes especificos de monitoreo ***//
    Route::get('monitoring/report/stard/{monitoring}', 'Report\ReportMonitoringController@consultFormStardMonitoring');
    Route::get('monitoring/report/vegetal/{monitoring}', 'Report\ReportMonitoringController@consultFormVegetalMonitoring');
    Route::get('monitoring/report/predial/{monitoring}', 'Report\ReportMonitoringController@consultFormTrackingPredial');
    Route::get('monitoring/report/evaluation/{monitoring}', 'Report\ReportMonitoringController@consultFormSupplierEvaluation'); //anexo 13
    //*** Bolsa ***//
    Route::get('generals/activities/pool/{id_pool}', 'PoolController@all_activities');
    Route::get('generals/detailactivities/pool/{id_pool}', 'PoolController@detail_activities');
    Route::put('generals/activities/pool/{id_pool}', 'PoolController@update_activities');
    Route::post('generals/activities/pool', 'PoolController@insert_activities');
    Route::post('generals/otherData/contractor', 'PoolController@addOrUpdateOtherCampsContractor');
    Route::post('generals/unforeseen/contractor', 'PoolController@addUnforeseenTariffContractor');
    Route::get('generals/unforeseen/contractor/{id_pool}', 'PoolController@getUnforeseenTariffContractor');
    Route::get('generals/first_report/budget/{id_pool}', 'PoolController@budgetReportPool');
    Route::get('generals/second_report/budget/{id_pool}', 'PoolController@budgetReportPoolContractor');

    Route::delete('pool/delete/{id}', 'PoolController@deleteFile');
    Route::get('pool/budget/process', 'PoolController@consultBudgetByProcess');
    Route::get('pool/budget/process/{id_pool}', 'PoolController@consultBudgetByPool');
    Route::get('pool/contract/{id}', 'PoolController@getContractByPoolId');
    Route::get('pool/download/{id}', 'PoolController@downloadFile');

    Route::resource('pool', 'PoolController');
    Route::put('pool/actions/contractor', 'PoolController@createSelectActionsPoolByContractor');
    Route::put('pool/actions/contractor/{id}', 'PoolController@updateSelectActionsPoolByContractor');
    Route::post('pool/actions/contract', 'PoolController@saveContract');

    //*** Comando y Control ***/
    Route::get('commandand/allassociated', 'CommandAndController@allAssociated');
    Route::get('commandand/budgets', 'CommandAndController@allBudget');
    Route::get('commandand/budgets/{year}', 'CommandAndController@yearAllBudget');
    Route::get('commandand/detailbudget/{id}', 'CommandAndController@detailBudget');
    Route::get('commandand/filter/{directive_id}/{id}/{year}', 'CommandAndController@filterAssociatedActivity');
    Route::get('commandand/commitment/{id}', 'CommandAndController@commitmentBudgetAnalyze');
    Route::get('commandand/search_contribution_shares/{id}', 'CommandAndController@shearhActionAsociated');
    Route::get('commandand/validateTaskAssociatedBudget/{id}', 'CommandAndController@validateTaskAssociatedBudget');


    Route::post('commandand/insert', 'CommandAndController@insertInversionAssociated');
    Route::post('commandand/update', 'CommandAndController@updateInversionAssociated');
    Route::post('commandand/transaction', 'CommandAndController@transalateInversionAssociated');
    Route::post('commandand/action_budget/associated', 'CommandAndController@insertActionBudgetAssociated');
    Route::delete('commandand/actionDelete', 'CommandAndController@actionDelete');

    //*** CRUD Metas **//
    Route::get('commandand/goalReadAll', 'CommandAndController@goalReadAll');
    Route::get('commandand/goalReadDetail/{id}', 'CommandAndController@goalReadDetail');
    Route::post('commandand/goalInsert', 'CommandAndController@goalInsert');
    Route::delete('commandand/goalDelete', 'CommandAndController@goalDelet');

    //*** Tareas abiertas ***//
    Route::get('generals/task/open', 'General\GeneralTaskOpenController@consultTaskOpen');
    Route::get('generals/taskOpen/getFile/{id}', 'General\GeneralTaskOpenController@getFiles');
    Route::get('generals/taskOpen/verifiedFiles/{id}', 'General\GeneralTaskOpenController@verifiedFiles');
    Route::get('generals/task/open/{id}', 'General\GeneralTaskOpenController@consultTaskOpenSpecific');
    Route::get('generals/task/Coordinate/{id}', 'General\GeneralTaskOpenController@bringCoordinates');
    Route::get('generals/task/getInfOpenExel/{task_id}/{type}', 'General\GeneralTaskOpenController@getInfOpenExel');
    Route::get('generals/getContribution/{type}', 'General\GeneralTaskOpenController@commandActivitesContribution');
    Route::get('generals/getContributionSpecie/{id_activite}', 'General\GeneralTaskOpenController@commandActivitesContributionSpecie');
    Route::get('generals/getComments/{type}/{task}', 'General\GeneralTaskOpenController@getComment');
    Route::get('generals/taskOpen/GeoMap/{id_task}', 'General\GeneralTaskOpenController@getGeoMap');
    Route::get('generals/filesFor/Forms/{task_id}', 'General\GeneralTaskOpenController@getDocumentForms');
    Route::post('generals/filesFor/Forms', 'General\GeneralTaskOpenController@insertDocumentForms');
    Route::post('generals/taskOpen/GeoMap', 'General\GeneralTaskOpenController@insertGeoMap');
    Route::post('generals/taskOpen/nextSubtype', 'General\GeneralTaskOpenController@nextTaskSubtype');
    Route::post('generals/addComments', 'General\GeneralTaskOpenController@addComment');
    Route::post('generals/taskOpen/insertDocument', 'General\GeneralTaskOpenController@insertDocumenttaskOpen');
    Route::post('generals/taskOpen/register', 'General\GeneralTaskOpenController@createTaskOpenSpecial');
    Route::post('generals/formTaskOpen/{id_task}', 'General\GeneralTaskOpenController@insertformTaskOpen');
    Route::post('generals/taskOpen/otherCamps', 'General\GeneralTaskOpenController@otherCamps');
    Route::delete('/generals/taskOpen/deleteFile/{id}', 'General\GeneralTaskOpenController@deleteFile');

    //*** Tarea abierta Comunicacion ***//
    Route::post('generals/formComunication', 'General\GeneralTaskOpenController@communicationForm');
    Route::get('generals/formComunication/{id_task}', 'General\GeneralTaskOpenController@getCommunicationForm');

    //*** Crud carta de intención ***//
    Route::put('generals/letter/intention/{letter_intention_id}', 'General\GeneralLetterIntentionController@updateLetterIntention');
    Route::get('generals/letter/intention', 'General\GeneralLetterIntentionController@consultLetterIntention');
    Route::get('generals/letter/intention/{letter_intent/tasksion_id}', 'General\GeneralLetterIntentionController@consultLetterIntentionSpecific');
    Route::get('generals/letter/intention/proccess/{id_tasks}', 'General\GeneralLetterIntentionController@validateLetterProccess');
    Route::get('generals/letter/form/{id_tasks}', 'General\GeneralLetterIntentionController@validateLetterProccess');

    //*** One Signal ***//
    Route::post('generals/playerId', 'General\GeneralOneSignalController@getPlayerIdOneSignal');
    Route::post('generals/getPlayerId', 'General\GeneralOneSignalController@getPlayerIdUserAuth');

    //*** Tareas de ejecucion ***//
    Route::get('execution', 'General\GeneralTaskExecutionController@consultTaskExecution');
    Route::get('execution/{id}', 'General\GeneralTaskExecutionController@consultTaskExecutionSpecific');
    Route::get('execution/consult/pool/actions/contractor', 'General\GeneralTaskExecutionController@consultActionsContractor');
    Route::get('execution/getMap/{id_task}', 'General\GeneralTaskExecutionController@getMapTaskExecution'); //mapa original
    Route::get('execution/GeoMap/{id_task}', 'General\GeneralTaskExecutionController@getGeoMapTaskExecution'); //mapa de tarea de ejecucion
    Route::get('execution/endTaskMeasurement/{id_task}', 'General\GeneralTaskExecutionController@endTaskMeasurement');
    Route::get('execution/validateSubtypeOn/{id_task}', 'General\GeneralTaskExecutionController@validateSubtypeOn');
    Route::post('execution/GeoMap', 'General\GeneralTaskExecutionController@loadMapTaskExecution');
    Route::post('execution/task', 'General\GeneralTaskExecutionController@registerTaskExecution');
    Route::post('execution/next/flow', 'General\GeneralTaskExecutionController@nextFlowTaskExecution');
    Route::post('execution/loadSig', 'General\GeneralTaskExecutionController@loadMapSigTaskExecution');
    Route::put('execution/task/{id}', 'General\GeneralTaskExecutionController@updateTaskExecution');


    //*** Usuarios que intervenieron en un procedimiento ***//
    Route::get('generals/users/intervention/process/{process_id}', 'ProcessController@listsUsersInterventionProcess');
    Route::get('generals/processDetail/{process_id}', 'ProcessController@processDetail');
    Route::get('generals/approved/task/execution/{id}', 'General\GeneralTaskExecutionController@approvedTaskExecution');
    Route::get('generals/percentage/task/execution/{process_id}', 'General\GeneralTaskExecutionController@percentageTaskExecution');

    //*** Coordinacion General y Procesos***/
    Route::get('generals/coordinating/budget/bycoordination', 'General\GeneralCoordinatorController@getBudgetByCoordination');
    Route::get('generals/coordinating/budget', 'General\GeneralCoordinatorController@CoordinatingBudget');
    Route::get('generals/comandProces/{id}', 'General\GeneralProcessController@ProcessBudeget');

    //*** Consultar listado de las tareas que intervino el usuario ***//
    Route::get('generals/task/history/intervention/user', 'General\GeneralTaskController@consultListTaskHistoryUser');

    //--- Metodos generales o globales---//
    Route::get('generals/departaments', 'General\GeneralMethodController@departaments');
    Route::get('generals/municipality/{departament_id}', 'General\GeneralMethodController@municipality');

    //--- Predios potenciales ---//
    Route::get('generals/property/consult/potential/{filter}', 'PotentialProperty\PotentialPropertyController@consultPropertiesPotentials');
    Route::post('potential/property', 'General\GeneralPropertyController@registerPropertyPotential');
    Route::post('potential/property/info', 'PotentialProperty\PotentialPropertyController@registerPotential');
    Route::get('potential/property/{id}', 'PotentialProperty\PotentialPropertyController@detailPotentialProperty');
    Route::post('potential/property/approved', 'PotentialProperty\PotentialPropertyController@approvedPotentialProperty');
    Route::post('potential/property/comment', 'PotentialProperty\PotentialPropertyController@propertyPotentialByComment');
    Route::get('potential/property/select/{id}', 'PotentialProperty\PotentialPropertyController@getSelectOfUserPotentialProperty');
    Route::get('potential/property/back/{id}', 'PotentialProperty\PotentialPropertyController@backPotentialPropertyUser');
    Route::get('potential/finalized/{id}', 'PotentialProperty\PotentialPropertyController@finalizedPotentialProperty');
    Route::get('potential/specific/info/{id}', 'PotentialProperty\PotentialPropertyController@consultInfoSpecificOfPollAndLetterIntention');
    Route::get('potential/consult/files/{id}', 'PotentialProperty\PotentialPropertyController@consultFilesPotentialPotential');
    Route::get('potential/consult/all/properties/approved', 'PotentialProperty\PotentialPropertyController@consultPotentialProperyApproved');
    Route::put('potential/coordinate/update', 'PotentialProperty\PotentialPropertyController@updateCoordinatePotentialProperty');
    Route::post('potential/delete/file', 'PotentialProperty\PotentialPropertyController@deleteFilePotentialProperty');
    Route::get('potential/consult/poll/{potential_id}', 'PotentialProperty\PotentialPropertyController@consultPollPotentialProperty');
    Route::get('potential/consult/letter/{potential_id}', 'PotentialProperty\PotentialPropertyController@consultLetterPotentialProperty');
    Route::put('potential/update/letter/poll', 'PotentialProperty\PotentialPropertyController@updateInfoPollLetter');
    Route::get('potential/delete/{potential_id}', 'PotentialProperty\PotentialPropertyController@deletePotentialProperty');
    Route::get('potential/real/no_process', 'PotentialProperty\PotentialPropertyController@potentialsRealNoProcess');

    //--- Guardar anexos de un archivo ---//
    Route::post('generals/load/files/attachment', 'General\GeneralFileController@saveAttachment');
    Route::get('generals/load/files/attachment/{id_file}', 'General\GeneralFileController@getAttachment');
    Route::get('generals/get/allFiles/{folder}/{url}', 'General\GeneralFileController@getallFiles64');
    Route::delete('generals/load/files/attachment/{id_file}', 'General\GeneralFileController@deleteAttachment');

    //--- Croquis ---//
    Route::get('generals/sketchPotentialProperty/{id}', 'General\GeneralPropertyController@consultSketchProcessProperty');

    //--- Presupuesto ---//
    Route::get('generals/budgetContractor/validate/{id_process}', 'PoolController@validatePoolByProcess');
    Route::get('generals/budgetExecution/validate/{id_process}', 'PoolController@validateExecutionByProcess');
    Route::get('generals/budgetExecution/validatePool/{id_pool}', 'PoolController@validateExecutionByPool');
    Route::get('generals/budgetExecutionRestoration/{id_process}', 'General\GeneralBudgetController@budgetExecutionRestoration');
    Route::get('generals/budgetContractorRestoration/{id_process}', 'General\GeneralBudgetController@budgetContractorRestoration');
    Route::get('generals/budgetRestoration/{id_process}', 'General\GeneralBudgetController@budgetActionRestoration');
    Route::get('generals/associateforBudget/{id_process}', 'General\GeneralBudgetController@associateforBudget');
    Route::get('generals/shearOriginResource/{id_process}', 'General\GeneralBudgetController@shearOriginResource');
    Route::post('generals/originOfResources', 'General\GeneralBudgetController@originOfResources');

    //--- Gestion Predial---//
    Route::post('generals/propertyManagement', 'Method\FunctionsSpecificController@propertyManagement');
    Route::get('generals/get/propertyManagement', 'Method\FunctionsSpecificController@getPropertyManagement');

    //-- Flujo PSA --//
    Route::post('generals/psa/budget', 'General\GeneralPsaController@insertBudget');
    Route::post('generals/temp', 'General\GeneralPsaController@calcSpeciesCommand');
    Route::post('generals/task/insertDocument/{type}', 'General\GeneralPsaController@insertDocumenttask');
    Route::get('generals/task/getDocument/{type}', 'General\GeneralPsaController@getFile');

    //-- Modulo Financiero --//
    Route::get('generals/financier/getContribution', 'General\GeneralFinancierController@getContribution');
    Route::get('generals/financier/programProject', 'General\GeneralFinancierController@programProject');
    Route::get('generals/financier/getCommandDetail', 'General\GeneralFinancierController@getCommandDetail');
    Route::get('generals/financier/getCommandDetail/{id}', 'General\GeneralFinancierController@getCommandDetailspecific');
    Route::get('generals/financier/getContributionSpecie', 'General\GeneralFinancierController@getContributionSpecie');
    Route::get('generals/financier/getLastLoadExel', 'General\GeneralFinancierController@getLastLoadExel');
    Route::get('generals/financier/getInfoLoadExel', 'General\GeneralFinancierController@getInfoLoadExel');
    Route::get('generals/financier/getAllIncomes', 'General\GeneralFinancierController@getAllIncomes');
    Route::post('generals/financier/getLoadExcelClasificate', 'General\GeneralFinancierController@getLoadExcelClasificate');
    Route::post('generals/financier/insertContributionSpecie', 'General\GeneralFinancierController@insertContributionSpecie');
    Route::post('generals/financier/insertContribution', 'General\GeneralFinancierController@insertContribution');
    Route::post('generals/financier/loadExcel', 'General\GeneralFinancierController@loadExcel');
    Route::post('generals/financier/updateContributionDetail', 'General\GeneralFinancierController@updateContributionDetail');
    Route::post('generals/financier/transactionDetail', 'General\GeneralFinancierController@transactionDetail');
    Route::post('generals/financier/income', 'General\GeneralFinancierController@createIncome');
    Route::put('generals/financier/income', 'General\GeneralFinancierController@updateIncome');

    Route::get('generals/associated', 'Associated\AssociatedController@getAssociatedByType');

    Route::resource('generals/financier/seedCapital', 'SeedCapital\SeedCapitalController');

    Route::resource('generals/financier/financingExpense', 'FinancingExpense\CvFinancingExpenseController');

    Route::get('generals/fileData', 'General\GeneralMapController@getFileData');
});
//---Formatos Excel---//
Route::get('generals/excel_report/budget/{type}/{id_pool}', 'PoolController@generalExelReportForPool');
Route::get('generals/excel/property', 'Method\ExcelFormatController@formatPropertyExcel');
Route::get('generals/taskOpen/dowloadSamples/{id_task}', 'General\GeneralTaskOpenController@dowloadSamplesTaskOpens');
Route::post('generals/taskOpen/dowloadSamples/{id_task}', 'General\GeneralTaskOpenController@dowloadSamplesTaskOpensFilterData');
Route::get('commandand/report/{year}', 'CommandAndController@ReportComandAndControllerDownload');

Route::group(['middleware' => ['auth']], function () {

    //--- Categorias ---//
    Route::get('category_all', 'General\GeneralUserController@category');

    //--- Proyectos ---//
    Route::get('listsTask/{project?}', 'Api\TaskController@listTasks');

    //--- Tareas ---//
    Route::get('tasks', 'Api\TaskController@allTasksLists');
    Route::get('tasks_show/{id}', 'Api\TaskController@TasksShow');

    //--- Predios ---//
    Route::get('property_correlation', 'General\GeneralPropertyCorrelationController@consultPropertyCorrelationData');
    Route::post('loadFiles', 'General\GeneralFileController@saveFiles');
    Route::get('properties', 'PropertyController@getPropertiesByProcess');

    //--- Monitoreos ---//
    Route::post('monitoring/files/send', 'General\GeneralMonitoringController@saveImgMonitoring');

    //---- Pruebas borrar ---//
    Route::get('fileBase64', 'General\GeneralFileController@convertFileToImageAfterBase64');

    //--- Registrar archivos de talonario con carta de intencion y tarea ---//
    Route::post('register/files/task/intention', 'LetterIntention\FilesTaskMeasurementController@registerFileTask');
    Route::get('consult/files/task/intention/{task_id}', 'LetterIntention\FilesTaskMeasurementController@consultFileTask');

    //--- Pago en proceso contractual ---//
    Route::post('request/pay/process/contractual', 'Pay\ProcessContractualController@requestPay');
    Route::get('consult/pay/process/contractual/{pool_id}', 'Pay\ProcessContractualController@consultPayProceesContractual');
    Route::get('select/financial/pay/process/contractual/{pool_id}', 'Pay\ProcessContractualController@selectPayByUserWithRoleFinancial');
    Route::post('flow/financial/pay/process/contractual', 'Pay\ProcessContractualController@flowCancelOrApproved');
    Route::delete('delete/file/financial/pay/process/contractual/{file_id}', 'Pay\ProcessContractualController@deleteFilesPayProcessContractual');

    //--- Archivos en base 64 ---//
    Route::post('generals/save/file/tasks/generals', 'General\GeneralTaskFileBase64Controller@registerFileTaskGeneralBase64');
    Route::get('generals/consult/files/base64/task/{task_id}', 'General\GeneralTaskFileBase64Controller@consultGeneralsTaskFile64');


    //--- Actualizar porcentajes de presupuesto por procedimiento ---//
    Route::put('generals/update/budget/porcent', 'General\GeneralBudgetController@updateBudgetByProcess');
});


/**
 * Consultas por filtros
 */
Route::post('filter/consult/task', 'Filter\FilterController@filterTasksGeneral');