<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

//--- Rutas principales ---//
Route::get('/', function () {
    return abort(404);
});

Route::get('/login', function () {
    return "Por favor inicie sesion";
})->name('login');


Route::post('forgot-password', 'Auth\AuthController@reqForgotPassword');
Route::post('update-password', 'Auth\AuthController@updatePassword');

//--- Logout ---//
Route::get('/logout', 'User\LoginController@logout');

//--- Consultar rol del usuario autenticado ---//
Route::get("/role", "General\GeneralAuthRolePermissionController@authRoleEntityPermission")->middleware('auth');

//--- Control de roles y autenticacion ---//
Route::group(['middleware' => ['role_entities_permission', 'auth']], function () {

    //--- Consulta generales ---//
    Route::get('generals/consultRoleContractorGuard', 'General\GeneralRoleController@consultCvRoleContractorGuard');
    Route::get('generals/consultRoleTeamGuard', 'General\GeneralRoleController@consultCvRoles');
    Route::get('generals/type/monitoring', 'General\GeneralMonitoringController@consultAllTypesMonitoring');
    Route::get('generals/butget/geojson/{task}', 'General\GeneralBudgetController@consultbudgetgeojson');
    Route::post('generals/loadFiles', 'General\GeneralFileController@saveFiles');
    Route::get('generals/user/{role_id}', 'General\GeneralRoleController@usersRole');
    Route::get('generals/all/roles', 'General\GeneralRoleController@consultCvRoleAll');
    Route::get('generals/files/project/{project}', 'General\GeneralFileController@consultImagesProject');
    Route::get('generals/files/task/{task}/{subType}', 'General\GeneralFileController@consultFilesTask');
    Route::get('generals/files/user/{id}', 'General\GeneralFileController@consultFilesUser');
    Route::get('generals/consultProjects', 'General\GeneralProjectController@consultProjects');
    Route::get('generals/consultTasksByProject/{project}', 'General\GeneralTaskController@consultTaskByProject');
    Route::post('generals/send/task/firm/guardTeam', 'General\GeneralFlowController@assignTaskGuardTeamFirm');
    Route::get('generals/type/contractor', 'General\GeneralUserController@consultTypeContractor');
    Route::get('generals/modality/contractor', 'General\GeneralUserController@consultModalityContractor');
    Route::get('generals/typecontracts', 'General\GeneralContractController@getContracts');

    //--- Consultar de reportes ---//
    Route::get('generals/minute/{task}', 'General\GeneralReportsController@cunsultforminute');
    Route::get('generals/managementreport', 'General\GeneralReportsController@generateManagementReport');
    Route::get('generals/goalsbyyear', 'General\GeneralReportsController@generateGoalByYearReport');
    Route::get('generals/forcontractor', 'General\GeneralReportsController@getExcelreportForContractor');
    Route::get('generals/format/individual_systems/{task}', 'General\GeneralReportsController@formatIndividualSystem');

    //--- Permisos ---//
    Route::get('entities_permission/consult', 'General\GeneralRoleEntityPermissionController@consultEntityPermision');

    //---Consultar programas, proyectos y actividades ---//
    Route::get('generals/programs', 'General\GeneralProcessController@consultPrograms');
    Route::get('generals/programs/projects/{id}', 'General\GeneralProcessController@consultProjects');
    Route::get('generals/programs/projects/activities/{id}', 'General\GeneralProcessController@consultProjectsActivities');

    //--- Actions good practices ---//
    Route::get('generals/actionsgp', 'General\GeneralActionsController@getActionsGoodPractices');
    Route::post('generals/actionsgp', 'General\GeneralActionsController@saveActionsGoodPractices');

    Route::get('generals/typeTaskByActivity/{process_id}', 'General\GeneralTaskController@consultTypeTaskByActivity');
    Route::get('generals/typeTaskByActivity/process/{id}', 'General\GeneralTaskController@consultTypeTaskByActivityProcess');

    //--- Proyectos ---//
    Route::resource('projects', 'ProjectController');

    //--- Usuarios ---//
    Route::get('users/all', 'User\UserController@consultAllUsers');
    Route::get('users/all/{id}', 'User\UserController@consultAllUsersByRol');
    Route::get('users/{id}', 'User\UserController@consultUserSpecific');
    Route::post('users', 'User\UserController@registerUser');
    Route::put('users/{id}', 'User\UserController@updateUser');
    Route::delete('users/{id}', 'User\UserController@deleteUser');
    Route::get('users/quota/{id}', 'User\UserController@consultUserGuardQuota');
    Route::put('users/quota/{id}', 'User\UserController@updateUserGuardQuota');

    //--- Tareas ---//
    Route::resource('tasks', 'TaskController');

    //--- Procedimiento ---//
    Route::resource('process', 'ProcessController');

    //--- Consulta actividades por procedimiento ---//
    Route::get('process/by/activities/{process_id}', 'ProcessController@consultActivitiesByProcess');

    //--- Programas ---//
    Route::resource('programs', 'ProgramController');

    //--- Mapas ---//
    Route::post('maps/task/geojson', 'General\GeneralMapController@updateMapGeoJson');
    Route::get('tasks/property/info/{task?}', 'PropertyController@consultPropertyInfo');
    Route::get('maps/task/geojson/{task?}', 'General\GeneralMapController@consultMapTask');

    //--- Approve ---//
    Route::post('tasks/approve', 'General\GeneralTaskController@approvedTask');
    Route::post('tasks/budget/approve', 'General\GeneralTaskController@approvedTaskBudget');

    //--- Budget ---//
    Route::get('tasks/consultBudgetByTask/{task?}', 'General\GeneralBudgetController@consultBudgetByTask');
    Route::get('tasks/consult/budget/all', 'General\GeneralBudgetController@consultBudgetAll');

    //--- Tareas por procedimiento ---//
    Route::get('tasks/process/{process}', 'General\GeneralTaskController@consultTaskByProcess');

    //--- perfil ---//
    Route::get('profile', 'Auth\AuthController@profile');

    //--- Balacen de cuota -> Corregir ruta ---//
    Route::get('tasks/quota/balance', 'General\GeneralBudgetController@propertyRealQuota');

    //--- Comentarios ---//
    Route::post('generals/commentsbyTask', 'General\GeneralCommentsbyTask@createCommentsbyTask');
    Route::get('generals/send/certificate/tradition/{task}', 'General\GeneralTaskController@sendTaskTraditionCertificate');

    //--- Cancelar acciones del procedimiento ---//
    Route::get('generals/cancel/process/task/map/property/{id}', 'General\GeneralFlowController@cancelProcessTaskByProperty');

    //--- Croquis ---//
    Route::post('sketch/insert', 'Sketch\SketchInitController@insert');
    Route::post('sketch/shearPointBasin', 'Sketch\SketchInitController@shearPointBasin');
    Route::post('sketch/insertProperty', 'Sketch\SketchInitController@insertSketchProperty');
    Route::post('generals/CommentPoint', 'Sketch\SketchInitController@CommentPoint');
    Route::get('generals/CommentPoint/{type}/{task_id}/{hash}', 'Sketch\SketchInitController@getCommentPoint');
});

//*** Control de solo autenticacion ***//
Route::group(['middleware' => ['auth']], function () {

    //--- Cuota de guarda cuenca ---//
    Route::get('subTypeTask', 'General\GeneralSubTypeTaskController@consultSubTypeTask');
    Route::get('commentsbyTask/{idTask}/{type}', 'General\GeneralCommentsbyTask@consultCommentsbyTask');
    Route::get('generals/total/quote', 'General\GeneralQuotesSaveController@consultQuotes');

    // --- Regresar tarea --- //
    Route::get('back/task/{id}', 'General\GeneralHistoryTaskController@backTaskByUser');

    //*** Consultar si el procedimiento ya cuenta con un predio potencial ***//
    Route::get('property/consult/potential/exist/{id}', 'General\GeneralPropertyController@consultProcessPropertyPotentialExist');

    //*** Eliminar archivo ***//
    Route::post('file/delete', 'General\GeneralFileController@deleteFiles');

    //*** Consultar información de la encuesta ***//
    Route::get('/poll/{predio?}', 'PropertyController@consultProperty');
    Route::post('/approved', 'General\GeneralTaskController@approvedTask');

    Route::post('generals/admin/delete_category', 'General\GeneralUserController@delete_category');
});
