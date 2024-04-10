<?php

namespace App\Http\Controllers\General;

use App\CvActionByActivity;
use App\CvAssociated;
use App\CvAssociatedContribution;
use App\CvBudget;
use App\CvBudgetByBudgetContractor;
use App\CvBudgetByBudgetExcution;
use App\CvCommunicationFormsJson;
use App\CvFinancierCommandDetails;
use App\CvFormatDetallCotractor;
use App\CvGeoJson;
use App\CvLetterIntention;
use App\CvMonitoring;
use App\CvOriginResource;
use App\CvOtherInfoContractor;
use App\CvPool;
use App\CvPoolActionByUser;
use App\CvProgram;
use App\CvProperty;
use App\CvTaskExecutionGeoMap;
use App\CvTaskExecutionUser;
use App\CvTaskOpen;
use App\CvTaskOpenBudget;
use App\CvTaskOpenSubType;
use App\CvTaskStatus;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvTask;
use App\CvUnits;
use App\CvBudgetActionMaterial;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Array_;
use function Zend\Diactoros\marshalUriFromSapi;

class GeneralReportsController extends Controller {

    //*** Consultar informacion de minuta ***//

    public function cunsultforminute($taskid) {

        //--- variable definida ---//
        $tbudget = 0;
        $info = array();
        $act = array();
        $bud = array();

        //--- Validar que la tarea exista ---//
        $task = CvTask::find($taskid);

        if (empty($task)) {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        } else {

            //--- Calcula presupuesto ---//

            foreach ($task->budget as $budget) {

                $presupuesto = CvBudgetActionMaterial::find($budget->action_material_id);
                $unidad = CvUnits::find($presupuesto->budgetPriceMaterial->unit_id);

                //--- cargar actividades por presupuesto ---//
                array_push($act, array(
                    //--- Actividades ---//

                    "action_name" => $presupuesto->action->name,
                    "material_name" => $presupuesto->budgetPriceMaterial->name,
                    "unit" => $unidad->name,
                    "amount" => $budget->length
                ));

                //--- valor de presupuesto separado solo si es necesario ---//

                array_push($bud, array(
                    //--- Actividades ---//

                    "budget_value" => $budget->value,
                ));

                $tbudget = $budget->value + $tbudget;
            }

            //--- Json de encuesta ---//
            $json = json_decode($task->property->info_json_general, true);

            //--- Datos  para minuta ---//
            $letter=json_decode($task->process[0]->letterIntention->form_letter, true);



            $budgetJson=new GeneralBudgetController();
            $allbudget=  $budgetJson->budgetActionRestoration( $task->process[0]->id);

            if (array_key_exists('contact', $json)) //georgi add
            {
                array_push($info, array(
                    "owner" => [
                        "owner_name" => $json["contact"]["contact_name"],
                        "identity_owner" => $json["contact"]["contact_id_card_number"],
                        "year" => $json["property_visit_date"]["year"],
                        "name_farm" => $json["property_name"],
                        "owner_email" => $json["property_name"],
                        "owner_phone" => $json["contact"]["contact_land_line_number"],
                        "owner_mobile" => $json["contact"]["contact_mobile_number"],
                        "municipality" => $json["municipality"],
                        "sidewalk" => $json["lane"],
                    ],
                    "embalse"=>$letter['embalse'],
                    "land_registration" => "matricula predio",
                    //--- escritura publica ---//
                    "public_deed" => [
                        "date" => "fecha",
                        "notary" => [
                            "number" => "numero",
                            "place" => "lugar"
                        ],
                    ],
                    "conservation_area"=>$allbudget['conservation_area'],
    
                    "total_area" => $json["economic_activity_in_the_property"]["property_area"],
                    "reservoir" => $json["property_reservoir"],
                    "date_day" => [
                        "day" => $json["property_visit_date"]["day"],
                        "month" => $json["property_visit_date"]["month"],
                        "year" => $json["property_visit_date"]["year"]
                    ],
                    "activities" => $act,
                    "total_budget" => $tbudget,
                    "micro_basin" => $json["micro_basin"],
                    "hydrological_source" => $json["hydrological_source"],
                    "date_letter_create" => $task->process[0]->letterIntention->created_at->format('Y-m-d H:i:s'),
                ));
            }
            return $info;
        }
    }

    public function formatIndividualSystem($idtasj) {

    }

    public function generateManagementReport(){

        //models required
        $programs = CvProgram::all();
        $associateds = CvAssociated::all();
        $formContractors = CvFormatDetallCotractor::all();
        $allTasksOpen = CvTaskOpen::all();
        $tasksOpenBudget = CvTaskOpenBudget::all();
        $users = User::all();
        $task = CvTask::all();
        $budget = CvBudget::all();
        $properties = CvProperty::all();
        $geoJsons = CvGeoJson::all();
        $geoMonitorings = CvMonitoring::all();
        $communicationFormJson = CvCommunicationFormsJson::all();
        $cvFinancierCommandDetails = CvFinancierCommandDetails::all();
        //
        $result = Array();

        $result['lineas_y_programas_estrategicos'] = $this->getProgramsWithProject($programs);

        $corporacionCuencaVerde = Array();
        $corporacionCuencaVerde['asociados_cuencaverde'] = $this->getAllAssociated($associateds);

        $corporacionCuencaVerde['data_num'] = $this->getAllApeopleByformContractor($formContractors, $cvFinancierCommandDetails);

        $dataInversionByAssociated = $this->getAssociatedWithInversion($associateds);
        $corporacionCuencaVerde['inversion_por_asociado'] = $dataInversionByAssociated['iversionPorAsociado'];
        $corporacionCuencaVerde['inversion_por_aliado'] =[];
        $corporacionCuencaVerde['total_inversion_aliados_asociados'] = $dataInversionByAssociated['inversionTotal'];
        $corporacionCuencaVerde['inversion_por_linea'] = $this->getInversionByLine($associateds, $programs);

        $result['corporacion_cuencaverde'] = $corporacionCuencaVerde;
        $result['aportes_capital_semilla_funcionamiento'] = [
            'CAPITAL_SEMILLA' => 'data from modul Financiero',
            'APORTES_FUNCIONAMIENTO' => 'data from modul Financiero'
        ];
        $result['gestion_administrativa'] = 'falta info';

        $arrEncuentroConActores = Array();
        $arrEncuentroConActores['encuentros_con_actores'] = $this->getEncountersWithActors($allTasksOpen, $tasksOpenBudget);
        $arrEncuentroConActores['experiencias_de_educacion_ambiental'] = $this->getExperiencesEnvironmental($allTasksOpen);
        $arrEncuentroConActores['plan_comunicaciones'] = $this->getCommunicationsPlan($allTasksOpen);
        $arrEncuentroConActores['guardacuencas'] = count($this->getUserByRol($users, 4));
        $arrEncuentroConActores['inversion'] = 'del modulo financiero';
        $result['cultura_del_agua'] = $arrEncuentroConActores;

        $dataAcuerdoDeIntervencion = $this->getAcuerdosIntervencion($task);
        $dataNacimientoHectareIversion = $this->getnacimientoHectareaInversion($budget);

        $arrDataHectareasConservation['rsc_lusitania'] = 0;
        $arrDataHectareasConservation['pago_por_servicios_ambientales'] = 0;
        $arrDataHectareasConservation['acuerdos_de_intervencion'] = $dataAcuerdoDeIntervencion['Measurement'];
        $arrDataHectareasConservation['total_hectareas_conservadas'] = $this->getTotalHectareasConservadas($budget);
        $arrDataHectareasConservation['total_hectareas_impactadas'] = 'Cuando se edita el predio se guarda el area, que sera el valor utilizado para calcular.';
        $arrDataHectareasConservation['municipios'] = count($this->getMunicipios($properties));
        $arrDataHectareasConservation['hectareas'] = $this->getHectareas($geoJsons, $geoMonitorings);
        $arrDataHectareasConservation['familias_beneficiadas'] = $dataAcuerdoDeIntervencion['numberOdFamilyGroups'];
        $arrDataHectareasConservation['siembra_arboles_nativos'] = 0;
        $arrDataHectareasConservation['diversidad_especies'] = 0;
        $arrDataHectareasConservation['nacimientos_en_proteccion'] = $dataNacimientoHectareIversion['nacimeintos'];
        $arrDataHectareasConservation['hectarea_de_nacimientos'] = $dataNacimientoHectareIversion['hecatreas'];
        $arrDataHectareasConservation['inversion'] = $dataNacimientoHectareIversion['inversion'];

        $arrHectareasDeConservacion['hectareas_de_conservacion'] = $arrDataHectareasConservation;

        $logroGestionPredial = $this->getLogrosGestionPredial($task);

        $arrHectareasDeConservacion['logro_gestion_predial'] = $logroGestionPredial['LogroGestionPredial'];
        $arrHectareasDeConservacion['logro_gestion_predial_rio_grande'] = $logroGestionPredial['LogroGestionPredialRioGrande'];
        $arrHectareasDeConservacion['logro_gestion_predial_la_fe'] = $logroGestionPredial['LogroGestionPredialLaFe'];
        $result['estrategias_de_conservacion'] = $arrHectareasDeConservacion;


        $arrDataSaneamiento['mantenimiento_stard']= 0;
        $arrDataSaneamiento['instalacion_stard']= 0;
        $arrDataSaneamiento['personas_beneficiadas']= 0;
        $arrDataSaneamiento['toneladas']= 0;
        $arrDataSaneamiento['metros_cubicos']= 0;
        $arrDataSaneamiento['inversion']= 0;
        $arrGestionRecursoHidrico['saneamiento_basico_integral'] = $arrDataSaneamiento;

        $arrDataControlErosion['metros_lineales_revegetalizados']= 0;
        $arrDataControlErosion['metros_lineales_cauce']= 0;
        $arrDataControlErosion['remocion_escombros']= 0;
        $arrDataControlErosion['inversion']= 0;
        $arrGestionRecursoHidrico['control_erosion_y_restauracion'] = $arrDataControlErosion;

        $result['gestion_recurso_hidrico'] = $arrGestionRecursoHidrico;


        $dataPrevencionDeforestacion = $this->getPrevencionDeforestacion($budget);
        $arrPracticasProduccionSostenible['prevencion_deforestacion'] = $dataPrevencionDeforestacion['prevencion'];
        $arrPracticasProduccionSostenible['ganaderia_sostenible'] = $dataPrevencionDeforestacion['ganaderia'];

        $result['practicas_produccion_sostenible'] = $arrPracticasProduccionSostenible;

        $dataGestionInformacionConocimiento['sistema_informacion_inversion'] = 0;
        $dataGestionInformacionConocimiento['humedales'] = 0;
        $dataGestionInformacionConocimiento['cta_inversion'] = 0;
        $dataGestionInformacionConocimiento['fondo_accion_inversion'] = 0;
        $dataGestionInformacionConocimiento['proteccion_hectareas'] = 0;
        $dataGestionInformacionConocimiento['escuelas'] = 0;
        $dataGestionInformacionConocimiento['rehabilitacion_stard'] = 0;
        $dataGestionInformacionConocimiento['solucion_potabilizacion'] = 0;
        $dataGestionInformacionConocimiento['stard_inversion'] = 0;
        $result['gestion_informacion_conocimiento'] = $dataGestionInformacionConocimiento;



        $result['monitoreo_seguimiento'] = $this->getCalidadDelAgua();
        $result['gestion_por_municipio'] = $this->getManagementByMunicipality($task, $communicationFormJson);


        return $result;
    }

    public function generateGoalByYearReport(){

        $arrBaseByYear = [
            'hectareas' => 0,
            'inversion' => 0,
            'year' => 0
        ];
        //necessary models
        $properties = CvProperty::all();

        $arrResult = Array();
        $arrYears = Array();
        foreach ($properties as $property) {
            $jsonProperty = json_decode($property->info_json_general, true);

            //TODO esto toca ponerlo ->where('task_sub_type_id', 33)
            foreach ($property->task as $task) {
                $inversion = 0;
                $hectareas = 0;

                $dateYear = $task->updated_at->year;

                if (!$task->budget->isEmpty()){
                    foreach ($task->budget as $budget) {
                        $priceMaterial = $budget->actionsMaterials->budgetPriceMaterial;

                        if($priceMaterial->unit_id == 3){
                            $inversion += $budget->value;
                            $hectareas += $budget->length;
                        }
                        else if ($priceMaterial->unit_id == 2){
                            $inversion += $budget->value;
                            $hectareas += ($budget->length / 10000);
                        }
                    }
                }

                if (!array_key_exists($jsonProperty['micro_basin'], $arrResult))
                    $arrResult[$jsonProperty['micro_basin']][$dateYear] = $arrBaseByYear;
                else if (!array_key_exists($arrResult[$jsonProperty['micro_basin']][$dateYear]['year'],$arrResult[$jsonProperty['micro_basin']]))
                    $arrResult[$jsonProperty['micro_basin']][$dateYear] = $arrBaseByYear;

                $arrResult[$jsonProperty['micro_basin']][$dateYear]['hectareas'] += $hectareas;
                $arrResult[$jsonProperty['micro_basin']][$dateYear]['inversion'] += $inversion;
                $arrResult[$jsonProperty['micro_basin']][$dateYear]['year'] += $dateYear;
            }
        }
        //  dd($arrResult);
        return $arrResult;
    }

    public function reportForContractor($from,$to)
    {
        $info=array();
        $pool_all=CvPool::all();
        foreach ($pool_all as $pool){
            $validate=0;
            $data_contractor=CvOtherInfoContractor::find($pool->contract_id);
            if (empty($data_contractor)){
                $validate=1;
            }
            if (count($pool->poolByProcess) <= 0){
                $validate=1;
            }
            if ($validate == 0){
                foreach ($pool->poolByProcess as $poolByProcess){
                    if ($poolByProcess->budget_id != null){
                        $budget=$poolByProcess->Budget;
                        $task= $budget->task;
                        $action=$budget->actionsMaterials->action;
                        $geomap=json_decode($task->geoJsonOne->geojson,true);
                        $other_data_contractor=json_decode($data_contractor->infojson,true);
                        $other_contractor=$data_contractor->first();
                        $property= json_decode( $budget->task->property->info_json_general, true);
                        $process =$poolByProcess->Process;
                        $area_intervention=$budget->length;
                        $area_contractor_liso=0;
                        $area_contractor_pua=0;
                        $area_contractor_aislamiento_plantula_liso=0;
                        $area_contractor_aislamiento_plantula_pua=0;
                        $area_contractor_establecimiento=0;
                        $area_contractor_enriquecimiento=0;
                        $area_execution_liso=0;
                        $area_execution_pua=0;
                        $area_execution_aislamiento_plantula_liso=0;
                        $area_execution_aislamiento_plantula_pua=0;
                        $area_execution_establecimiento=0;
                        $area_execution_enriquecimiento=0;
                        $area_contractor_facture=0;
                        $area_execution_facture=0;
                        $area_execution_intervention=0;
                        $estade_execution=null;
                        $subtipo_execution=null;
                        $associated_contribution=array();
                        foreach ($geomap['features'] as $map){
                            if ($budget->hash_map != null){
                                list($area_contractor_liso, $area_contractor_pua, $area_contractor_aislamiento_plantula_liso, $area_contractor_aislamiento_plantula_pua, $area_contractor_establecimiento, $area_contractor_enriquecimiento, $area_intervention) = $this->actionBySecondBudgetContractor($action, $budget, $map, $area_contractor_liso, $area_contractor_pua, $area_contractor_aislamiento_plantula_liso, $area_contractor_aislamiento_plantula_pua, $area_contractor_establecimiento, $area_contractor_enriquecimiento, $area_intervention,1);
                            }
                        }
                        $poolactions=CvPoolActionByUser::where('pool_by_process_id',$poolByProcess->id);
                        if ($poolactions->exists()){
                            $validate_taskexecution=CvTaskExecutionUser::where('pool_contractor_id',$poolactions->first()->id);
                            if ($validate_taskexecution->exists()){
                                $user_task_execution=$validate_taskexecution->first();
                                $estade_execution= CvTaskStatus::find( $user_task_execution->taskExecution->task_status_id)->first()->name;
                                $subtipo_execution= CvTaskOpenSubType::find( $user_task_execution->taskExecution->task_open_sub_type_id)->first()->name;
                                $validate_GeoMap= CvTaskExecutionGeoMap::where('task_execution_id',$user_task_execution->taskExecution->id);
                                $validate_budget_execution= CvBudgetByBudgetExcution::where('task_execution_id',$user_task_execution->taskExecution->id);
                                if ($validate_GeoMap->exists()){
                                    $execution_GeoMap=json_decode($validate_GeoMap->get()->last()->mapjson,true);
                                    $jsonmaptwo= json_decode( $execution_GeoMap,true) ;
                                    if (array_key_exists('features',$jsonmaptwo)){
                                        foreach ($jsonmaptwo['features'] as $GeoMap){
                                            if ($budget->hash_map != null){
                                                list($area_execution_liso, $area_execution_pua, $area_execution_aislamiento_plantula_liso, $area_execution_aislamiento_plantula_pua, $area_execution_establecimiento, $area_execution_enriquecimiento, $area_execution_intervention) = $this->actionBySecondBudgetContractor($action, $validate_budget_execution->first(), $GeoMap,$area_execution_liso, $area_execution_pua, $area_execution_aislamiento_plantula_liso, $area_execution_aislamiento_plantula_pua, $area_execution_establecimiento, $area_execution_enriquecimiento, $area_execution_intervention,2);
                                            }
                                        }
                                    }
                                }
                                if ($validate_budget_execution->exists()){
                                    $budget_execution=$validate_budget_execution->first();
                                    $val_exe=$budget_execution->price_execution;
                                    $val_exe_20=($budget_execution->price_execution*20)/100;
                                    $val_exe_19=($budget_execution->price_execution*19)/100;
                                    $val_exe_5=($val_exe_19*5)/100;
                                    $area_execution_facture=$val_exe+$val_exe_20+$val_exe_19+$val_exe_5;
                                }
                            }
                        }
                        $validate_budget_contractor= CvBudgetByBudgetContractor::where('budget_id',$budget->id);
                        if ($validate_budget_contractor->exists()){
                            $budget_contractor=$validate_budget_contractor->first();
                            $val_exe=$budget_contractor->price_contractor;
                            $val_exe_20=($budget_contractor->price_contractor*20)/100;
                            $val_exe_19=($budget_contractor->price_contractor*19)/100;
                            $val_exe_5=($val_exe_19*5)/100;
                            $area_contractor_facture=$val_exe+$val_exe_20+$val_exe_19+$val_exe_5;
                        }
                        $validateOriginResource=CvOriginResource::where('budget_id',$budget->id);
                        if ($validateOriginResource->exists()){
                            $contributions=$validateOriginResource->first()->detailOriginResource;
                            foreach ($contributions as $contri){
                                $contri->associatedContribution->associated;
                                array_push($associated_contribution,$contri->associatedContribution);
                            }
                        }

                        $division = 0;
                        if($area_contractor_facture != 0)
                            $division = $area_execution_facture / $area_contractor_facture;

                        if (date(date($other_data_contractor['dateInit'], strtotime("-1 month")) . ' 00:00:00', time()) >=  $from  && date($other_data_contractor['dateInit'] . ' 23:59:59', time()) <=  $to  ){
                            array_push($info,array(
                                'property_id'=>$process->potentialProperty->id,
                                'property_name'=>$process->potentialProperty->property_name,
                                'reservoir'=>$property['property_reservoir'],
                                'municipality'=>$property['municipality'],
                                'lane'=>$property['lane'],
                                'date_agreement_init'=>$other_data_contractor['dateInit'],
                                'date_agreement_end'=>$other_data_contractor['dateEnd'],
                                'user_name'=>$other_contractor->user->name,
                                'contractor_number'=>$other_data_contractor['numContact'],
                                'date__year_contractor'=>$other_contractor->created_at->format('Y'),
                                'intervention_area'=>$area_intervention,
                                'estade_execution'=>$estade_execution,
                                'subtipe_execution'=>$subtipo_execution,
                                'associated_contributions'=>$associated_contribution,
                                'area_contractor_liso'=>$area_contractor_liso,
                                'area_contractor_pua'=>$area_contractor_pua,
                                'area_contractor_aislamiento_plantula_liso'=>$area_contractor_aislamiento_plantula_liso,
                                'area_contractor_aislamiento_plantula_pua'=>$area_contractor_aislamiento_plantula_pua,
                                'area_contractor_establecimiento'=>$area_contractor_establecimiento,
                                'area_contractor_enriquecimiento'=>$area_contractor_enriquecimiento,
                                'area_execution_liso'=>$area_execution_liso,
                                'area_execution_pua'=>$area_execution_pua,
                                'area_execution_aislamiento_plantula_liso'=>$area_execution_aislamiento_plantula_liso,
                                'area_execution_aislamiento_plantula_pua'=>$area_execution_aislamiento_plantula_pua,
                                'area_execution_establecimiento'=>$area_execution_establecimiento,
                                'area_execution_enriquecimiento'=>$area_execution_enriquecimiento,
                                'area_execution_intervention'=>$area_execution_intervention,
                                'execution_facture'=>$area_execution_facture,
                                'contractor_facture'=>$area_contractor_facture,
                                'total_contractor_facture'=>$area_contractor_facture - $area_execution_facture,
                                'avance_contract_porcent'=>$division,
                            ));
                        }
                    }
                }
            }
        }
        return $info;
    }
    public function getExcelreportForContractor(Request $dates)
    {
        $from = date(date($dates->from, strtotime("-1 month")) . ' 00:00:00', time()); //need a space after dates.
        $to = date($dates->to . ' 23:59:59', time());

           $current = $this->reportForContractor($from,$to);
        $this->downloadExeclReportForPool($current);
    }

    private function downloadExeclReportForPool($data)
    {
        Excel::create('Laravel Excel', function ($excel) use ($data) {
            $excel->sheet('Productos', function ($sheet) use ($data) {
                $i = 1;
                $sheet->row($i, [
                    'ID_PREDIO', 'NOMBRE_PREDIO', 'EMBALSE', 'MUNICIPIO', 'VEREDA','FECHA_DE_ACUERDO','FECHA_TERMINACION_OBRA','CONTRATISTA','NUMERO_CONTRATO','AÑO_CONTRATO','AREA_INTERVENIDA','ESTADO_DE_ACCIONES','ASOCIADO_DE_PROYECTO','TIPO','CONTRATO_AREA_LISO','CONTRATO_AREA_PUA','CONTRATO_AREA_AISLAMIENTO_PLANTULA_LISO','CONTRATO_AREA_AISLAMIENTO_PLANTULA_PUA','CONTRATO_AREA_ESTABLECIMIENTO','CONTRATO_AREA_ENRIQUECIMIENTO','EJECUCION_AREA_LISO','EJECUCION_AREA_PUA','EJECUCION_AREA_AISLAMIENTO_PLANTULA_LISO','EJECUCION_AREA_AISLAMIENTO_PLANTULA_PUA','EJECUCION_AREA_ESTABLECIMIENTO','EJECUCION_AREA_ENRIQUECIMIENTO','FACTURA_EJECUTADA','FACTURA_CONTRATADA','TOTAL_FACTURA','PORCENTAJE_AVANCE'
                ]);

                foreach ($data as $index => $user) {
                    foreach ($user['associated_contributions'] as $index => $info) {

                        $i = $i + 1;
                        $sheet->row($index + 2, [
                            $user['property_id'], $user['property_name'], $user['reservoir'], $user['municipality'], $user['lane'], $user['date_agreement_init'], $user['date_agreement_end'], $user['user_name'], $user['contractor_number'], $user['date__year_contractor'], $user['intervention_area'], $user['estade_execution'], $info['associated']['name'], $info['associated']['type'], $user['area_contractor_liso'], $user['area_contractor_pua'], $user['area_contractor_aislamiento_plantula_liso'], $user['area_contractor_aislamiento_plantula_pua'], $user['area_contractor_establecimiento'], $user['area_contractor_enriquecimiento'], $user['area_execution_liso'], $user['area_execution_pua'], $user['area_execution_aislamiento_plantula_liso'], $user['area_execution_aislamiento_plantula_pua'], $user['area_execution_establecimiento'], $user['area_execution_enriquecimiento'], $user['execution_facture'], $user['contractor_facture'], $user['total_contractor_facture'], $user['avance_contract_porcent']
                        ]);
                    }
                }

            });
        })->export('xls');
    }

    private function getProgramsWithProject($programs){

        foreach ($programs as $program) {
            $program->programByProject;
        }
        return $programs;
    }
    private function getAllAssociated($associateds){
        $allAssociateds = Array();
        foreach ($associateds as $associated) {
            array_push($allAssociateds,['name' => $associated->name] );
        }

        return $allAssociateds;
    }
    private function getAllApeopleByformContractor($formContractors, $cvFinancierCommandDetails){
        $totalpeople = 0;
        $cantMens = 0;
        $cantWomens = 0;
        $peopleLinked = 0;
        $peopleProvisionOfService = 0;

        foreach ($formContractors as $formContractor) {
            $json = json_decode($formContractor->form_contractor);
            $cantMens += $json->men;
            $cantWomens += $json->women;
            $totalpeople = $totalpeople + ($json->women + $json->men);
        }
        if($totalpeople > 0){
            $percentWomen = ($cantWomens * 100) / $totalpeople;
            $percentMen = ($cantMens * 100) / $totalpeople;
        }
        else {
            $percentWomen = 0;
            $percentMen = 0;
        }

        foreach ($cvFinancierCommandDetails as $cvFinancierCommandDetail) {
            if($cvFinancierCommandDetail->benefit_factor != null & $cvFinancierCommandDetail->benefit_factor > 0){
                $peopleLinked += 1;
            } else {
                $peopleProvisionOfService += 1;
            }
        }

        return [
            'personas_empleadas' => $totalpeople,
            'personas_vinculadas' => $peopleLinked,
            'personas_prestacion_servicio' => $peopleProvisionOfService,
            'porcentaje_hombres' => $percentMen,
            'porcentaje_mujeres' => $percentWomen,
        ];
    }
    private function getAssociatedWithInversion($arrAssociated){

        $totalInversion = 0;
        // TODO get associated by type -----    $arrAssociated->where('','')->get()
        foreach ($arrAssociated as $associated) {
            $totalByAssociated = 0;
            foreach ($associated->getContibutions as $ontibution) {
                if ($ontibution->type == 1)
                    $totalByAssociated += $ontibution->inversion;
                else
                    $totalByAssociated += $ontibution->inversion_species;
            }
            $associated['inversion'] = $totalByAssociated;
            $totalInversion += $totalByAssociated;
            $associated['relations'] = [];
        }

        return [
            'iversionPorAsociado' => $arrAssociated,
            'inversionTotal' => $totalInversion,
        ];
    }
    private function getInversionByLine($arrAssociated, $programs){

        $totalInversionAssociaties = $this->getTotalInversionAllAssociated($arrAssociated);

        foreach ($programs as $program) {
            $valor = 0;
            foreach ($program->programByProject as $project) {
                foreach ($project->projectActities as $activity) {
                    foreach ($activity->associatedContribution as $associatedContribution) {
                        if ($associatedContribution->type == 1)
                            $valor = $valor + $associatedContribution->inversion;
                        else
                            $valor = $valor + $associatedContribution->inversion_species;
                    }
                }
            }
            $program['inversion'] = $valor;
            $program['percentage'] = ($valor * 100) / $totalInversionAssociaties;
            $program['relations'] = [];
        }
        return $programs;
    }
    private function getTotalInversionAllAssociated($associateds){
        $totalInversion = 0;
        $arrInversionAssociates = $this->getAssociatedWithInversion($associateds)['iversionPorAsociado'];
        foreach ($arrInversionAssociates as $arrInversionAssociate) {
            $totalInversion += $arrInversionAssociate['inversion'];
        }
        return $totalInversion;
    }
    private function getEncountersWithActors($allTasksOpen, $tasksOpenBudget){
        $arrEncuentroConActores = Array();

        $tasksOpenEncountersWithActors = $allTasksOpen->where('task_open_sub_type_id','=',6);
        $arrEncuentroConActores['talleres'] = $tasksOpenEncountersWithActors->count();

        $countPeople = 0;
        $inversion = 0;

        foreach ($tasksOpenEncountersWithActors as $tasksOpenEncountersWithActor) {
            foreach ($tasksOpenEncountersWithActor->getFormCommunication(1)->get() as $form) {
                $jsonForm = json_decode($form->formjson) ;
                $countPeople += $jsonForm->number_attendees;
            }

            $taskOpenBudgets = $tasksOpenBudget->where('task_open_id','=', $tasksOpenEncountersWithActor->id);
            foreach ($taskOpenBudgets as $taskOpenBudge) {
                $inversion += $taskOpenBudge->amount;
            }
        }

        $arrEncuentroConActores['personas'] = $countPeople;
        $arrEncuentroConActores['inversion'] = $inversion;
        return $arrEncuentroConActores;
    }
    private function getExperiencesEnvironmental($allTasksOpen){
        $arrExperiencesEnvironmental = Array();

        $experencesEnvironmentals = $allTasksOpen->where('task_open_sub_type_id','=',8);
        $arrExperiencesEnvironmental['experiencias'] = $experencesEnvironmentals->count();

        $countPeople = 0;
        $numTrees = 0;
        foreach ($experencesEnvironmentals as $experencesEnvironmental) {
            foreach ($experencesEnvironmental->getFormCommunication(1)->get() as $form) {
                $jsonForm = json_decode($form->formjson) ;
                $countPeople += $jsonForm->number_attendees;
                $numTrees += $jsonForm->number_trees;
            }
        }

        $arrExperiencesEnvironmental['personas'] = $countPeople;
        $arrExperiencesEnvironmental['arboles'] = $numTrees;
        return $arrExperiencesEnvironmental;
    }
    private function getCommunicationsPlan($allTasksOpen){
        $arrCommunicationsPlan = Array();

        $arrTasksOpens = $allTasksOpen->where('task_open_sub_type_id','=',7);

        $inversion = 0;
        foreach ($arrTasksOpens as $arrTasksOpen) {
            $taskBudgets = CvTaskOpenBudget::where('task_open_id', $arrTasksOpen->id)->get();
            foreach ($taskBudgets as $taskBudget) {
                $inversion += $taskBudget->amount;
            }
        }
        $arrCommunicationsPlan['inversion'] = $inversion;
        return $arrCommunicationsPlan;
    }
    private function getLogrosGestionPredial($task){
        $arrLaFe = Array();
        $arrRioGrande = Array();
        $arrLogroGP = Array();

        $arrLaFe['acuerdos_de_intervencion'] = 0;
        $arrLaFe['hectareas_impactadas'] = 0;
        $arrLaFe['hectareas_intervenidas'] = 0;
        $arrLaFe['metros_lineales'] = 0;
        $arrLaFe['hectareas_de_bosque'] = 0;
        $arrLaFe['hectareas_de_bosque_protegido'] = 0;
        $arrLaFe['nacimientos_agua_protegido'] = 0;



        $arrRioGrande['acuerdos_de_intervencion'] = 0;
        $arrRioGrande['hectareas_impactadas'] = 0;
        $arrRioGrande['hectareas_intervenidas'] = 0;
        $arrRioGrande['metros_lineales'] = 0;
        $arrRioGrande['hectareas_de_bosque'] = 0;
        $arrRioGrande['hectareas_de_bosque_protegido'] = 0;
        $arrRioGrande['nacimientos_agua_protegido'] = 0;


        $dataTasks = $task->where('task_sub_type_id', 33);
        foreach ($dataTasks as $dataTask) {
            $budget = $dataTask->budget;
            $jsonData = json_decode($dataTask->property->info_json_general);
            if ($jsonData->micro_basin == 'La Fe'){
                $dataByBudget = $this->getHectareasIntervenidas($budget);

                $arrLaFe['hectareas_impactadas'] = 0;//TODO ajuste
                $arrLaFe['acuerdos_de_intervencion'] += 1;
                $arrLaFe['hectareas_intervenidas'] = $dataByBudget['hectareas'];
                $arrLaFe['metros_lineales'] = $dataByBudget['metrosLineal'];
                $arrLaFe['hectareas_de_bosque'] = $dataByBudget['HectareasBosqueRestaurado'];
                $arrLaFe['hectareas_de_bosque_protegido'] = $dataByBudget['HectareasBosqueRibera'];
                $arrLaFe['nacimientos_agua_protegido'] = $dataByBudget['nacimientos'];


            }
            else if ($jsonData->micro_basin == 'Rio Grande'){
                $dataByBudget = $this->getHectareasIntervenidas($budget);

                $arrRioGrande['hectareas_impactadas'] = 0;//TODO ajuste
                $arrRioGrande['acuerdos_de_intervencion'] += 1;
                $arrRioGrande['hectareas_intervenidas'] = $dataByBudget['hectareas'];
                $arrRioGrande['metros_lineales'] = $dataByBudget['metrosLineal'];
                $arrRioGrande['hectareas_de_bosque'] = $dataByBudget['HectareasBosqueRestaurado'];
                $arrRioGrande['hectareas_de_bosque_protegido'] = $dataByBudget['HectareasBosqueRibera'];
                $arrRioGrande['nacimientos_agua_protegido'] = $dataByBudget['nacimientos'];
            }

        }

        $arrLogroGP['hectareas_impactadas'] = $arrLaFe['hectareas_impactadas'] + $arrRioGrande['hectareas_impactadas'];
        $arrLogroGP['acuerdos_de_intervencion'] = $arrLaFe['acuerdos_de_intervencion'] + $arrRioGrande['acuerdos_de_intervencion'];
        $arrLogroGP['hectareas_intervenidas'] = $arrLaFe['hectareas_intervenidas'] + $arrRioGrande['hectareas_intervenidas'];
        $arrLogroGP['metros_lineales'] = $arrLaFe['metros_lineales'] + $arrRioGrande['metros_lineales'];
        $arrLogroGP['hectareas_de_bosque'] = $arrLaFe['hectareas_de_bosque'] + $arrRioGrande['hectareas_de_bosque'];
        $arrLogroGP['hectareas_de_bosque_protegido'] = $arrLaFe['hectareas_de_bosque_protegido'] + $arrRioGrande['hectareas_de_bosque_protegido'];
        $arrLogroGP['nacimientos_agua_protegido'] = $arrLaFe['nacimientos_agua_protegido'] + $arrRioGrande['nacimientos_agua_protegido'];

        return [
            'LogroGestionPredial' => $arrLogroGP,
            'LogroGestionPredialRioGrande' => $arrRioGrande,
            'LogroGestionPredialLaFe' => $arrLaFe
        ];
    }
    private function getAcuerdosIntervencion($tasks){

        $allTasks = $tasks->where('task_sub_type_id', 33);
        $AllMeasurement = 0;
        $numberOdFamilyGroups = 0;

        foreach ($allTasks as $allTask) {
            $jsonDataProperti = json_decode($allTask->property->info_json_general);
            if ($jsonDataProperti->socio_economic_information->number_of_family_groups != null)
                $numberOdFamilyGroups += $jsonDataProperti->socio_economic_information->number_of_family_groups;

            foreach ($allTask->budget as $budget) {
                $budgetPriceMaterial = $budget->actionsMaterials->budgetPriceMaterial;

                if($budgetPriceMaterial->unit_id == 2){
                    $AllMeasurement += $budgetPriceMaterial->measurement / 10000;
                }
                else if($budgetPriceMaterial->unit_id == 3){
                    $AllMeasurement += $budgetPriceMaterial->measurement;
                }
            }
        }

        return [
            'Measurement' => $AllMeasurement,
            'numberOdFamilyGroups' => $numberOdFamilyGroups
        ];
    }
    private function getMunicipios($properties){

        $arrDataResponse = Array();

        foreach ($properties as $property) {
            $arrData = json_decode($property->info_json_general, true);
            if (array_key_exists('municipality', $arrData)){
                if($arrData['municipality'] != null){
                    array_push($arrDataResponse, $arrData['municipality']);
                }
            }
        }

        return array_unique($arrDataResponse);
    }
    private function getHectareas($geoJsons, $geoMonitorings){
        $numHectareas = 0;
        $allHectareas = $geoMonitorings->where('type_monitoring_id', 2);

        foreach ($geoJsons as $allGeoJson) {
            $arrGeoJson = json_decode($allGeoJson->geojson, true);
            foreach ($arrGeoJson['features'] as $arrGeoJson) {
                foreach ($allHectareas as $allHectarea) {
                    if ($arrGeoJson['properties']['hash'] == $allHectarea->hash_map){
                        if (array_key_exists('AREA_HA', $arrGeoJson['properties']) ){
                            $numHectareas += $arrGeoJson['properties']['AREA_HA'];
                        } else if (array_key_exists('LONGITUD_M', $arrGeoJson['properties'])){
                            $numHectareas += $arrGeoJson['properties']['LONGITUD_M'];
                        }
                    }
                }
            }
        }

        return $numHectareas;
    }
    private function getnacimientoHectareaInversion($allBudgets){
        $numHectareas = 0;
        $numNacimineto = 0;
        $numInversion = 0;

        foreach ($allBudgets as $allBudget) {
            $apellido = $allBudget->actionsMaterials->budgetPriceMaterial;
            if ($apellido->last_name != null & $apellido->last_name == 'nacimiento') {
                $numNacimineto += 1;
                $numInversion += $allBudget->value;

                if($apellido->unit_id == 3){
                    $numHectareas += $allBudget->length;
                }
                else if ($apellido->unit_id == 2){
                    $numHectareas += $allBudget->length / 10000;
                }
            }
        }

        return [
            'hecatreas' => $numHectareas,
            'nacimeintos' => $numNacimineto,
            'inversion' => $numInversion,
        ];
    }
    private function getHectareasIntervenidas($budgets){
        $hectareas = 0;
        $metrosLinealRibera = 0;
        $hectareasBosqueRestaurado = 0;
        $hectareasBosqueRibera = 0;
        $numNacimientos = 0;

        foreach ($budgets as $budget) {
            $priceMaterial = $budget->actionsMaterials->budgetPriceMaterial;

            if($priceMaterial->unit_id == 2 ){
                $hectareas += ($budget->length / 10000);
            }
            else if ($priceMaterial->unit_id == 3){
                $hectareas += $budget->length;
            }

            if ($priceMaterial->last_name == 'ribera' & $priceMaterial->unit_id == 1){
                $metrosLinealRibera += $budget->length;
            }

            if ($priceMaterial->last_name == 'nacimiento'){
                $numNacimientos += 1;
            }

            if ($priceMaterial->last_name == 'ribera' & ($priceMaterial->unit_id == 2 || $priceMaterial->unit_id == 3)){
                if ($priceMaterial->unit_id == 2)
                    $hectareasBosqueRibera += ($budget->length / 10000);
                else if ($priceMaterial->unit_id == 3)
                    $hectareasBosqueRibera += $budget->length;
            }

            if ($priceMaterial->last_name == 'establecimiento' || $priceMaterial->last_name == 'enriquecimiento' || $priceMaterial->last_name == 'aislamiento con plantulas'){
                if($priceMaterial->unit_id == 2 )
                    $hectareasBosqueRestaurado += ($budget->length / 10000);
                else if ($priceMaterial->unit_id == 3)
                    $hectareasBosqueRestaurado += $budget->length;
            }
        }

        return [
            'hectareas' => $hectareas,
            'metrosLineal' => $metrosLinealRibera,
            'HectareasBosqueRestaurado' => $hectareasBosqueRestaurado,
            'HectareasBosqueRibera' => $hectareasBosqueRibera,
            'nacimientos' => $numNacimientos,
        ];
    }
    private function getPrevencionDeforestacion($budget) {

        $estufasEficientes = 0;
        $huertosLenieros = 0;
        $inversion = 0;

        $bebederosAhorradores = 0;
        $tanquesAlmacenamiento = 0;
        $metrosLineales = 0;
        $arbolesDispersos = 0;
        $pasosGanado = 0;
        $inversionGanadera = 0;

        $allBudgets = $budget->where('good_practices', 1);

        foreach ($allBudgets as $allBudget) {
            $dataPriceMaterial = $allBudget->actionsMaterials->budgetPriceMaterial;
            if($dataPriceMaterial->last_name == 'estufas eficientes'){
                $estufasEficientes += 1;
                $inversion += $allBudget->value;
            }
            else if($dataPriceMaterial->last_name == 'huertos lenieros'){
                $huertosLenieros += 1;
                $inversion += $allBudget->value;
            }else if($dataPriceMaterial->last_name == 'bebederos ahorradores'){
                $bebederosAhorradores += 1;
                $inversionGanadera += $allBudget->value;
            }else if($dataPriceMaterial->last_name == 'tanques de almacenamiento'){
                $tanquesAlmacenamiento += 1;
                $inversionGanadera += $allBudget->value;
            }else if($dataPriceMaterial->last_name == 'cercos vivos'){
                $metrosLineales += $allBudget->length;
                $inversionGanadera += $allBudget->value;
            }else if($dataPriceMaterial->last_name == 'arboles dispersos'){
                $arbolesDispersos += $allBudget->length * $dataPriceMaterial->measurement;
                $inversionGanadera += $allBudget->value;
            }else if($dataPriceMaterial->last_name == 'pasos de ganado'){
                $pasosGanado += 1;
                $inversionGanadera += $allBudget->value;
            }
        }

        $prevencionDeforestacion = [
            'estufas_eficientes' => $estufasEficientes,
            'huertos_lenieros' => $huertosLenieros,
            'inversion' => $inversion
        ];
        $ganaderiaSostenible = [
            'bebederos_ahorradores' => $bebederosAhorradores,
            'tanques_almacenamiento' => $tanquesAlmacenamiento,
            'cercos_vivos' => $metrosLineales,
            'arboles_dispersos' => $arbolesDispersos,
            'pasos_ganado' => $pasosGanado,
            'inversion' => $inversionGanadera,
        ];
        return [
            'prevencion' => $prevencionDeforestacion,
            'ganaderia' => $ganaderiaSostenible,
        ];
    }
    private function getCalidadDelAgua(){

        $committed = 0;
        $programMyS = CvProgram::find(4);
        $programByProjects = $programMyS->programByProject;
        foreach ($programByProjects as $programByProject) {
            $projects = $programByProject->projectActities;
            foreach ($projects as $project) {
                $associatedContributions = $project->associatedContribution;
                foreach ($associatedContributions as $associatedContribution) {
                    $committed += $associatedContribution->committed;
                }
            }
        }
        return[
            'calidad_agua_rio_geande' => 0,
            'calidad_agua_la_fe' => 0,
            'inversion' => $committed,
        ];
    }
    private function getManagementByMunicipality($tasks, $communicationFormJson){

        $arrDataBase = [
            'acuerdos_intervencion' => 0,
            'hectareas_impactadas' => 0,
            'hectareas_intervenidas' => 0,
            'metros_lineales_ribera' => 0,
            'encuentro_actores' => 0,
            'mantenimiento_stard' => 0,
            'inversion' => 0,
        ];

        $allTaksAcuerdosIntervencion = $tasks->where('task_sub_type_id', 33);

        $arrMunicipalities = Array();
        foreach ($allTaksAcuerdosIntervencion as $task) {
            $formJson = json_decode($task->property->info_json_general);
            if (!array_key_exists($formJson->municipality, $arrMunicipalities)){
                $arrMunicipalities[$formJson->municipality] = $arrDataBase;
            }
            $arrMunicipalities[$formJson->municipality]['acuerdos_intervencion'] += 1;

            $budgets = $task->budget;
            foreach ($budgets as $budget) {
                $priceMAterial = $budget->actionsMaterials->budgetPriceMaterial;
                if ($priceMAterial->unit_id == 2){
                    $arrMunicipalities[$formJson->municipality]['hectareas_impactadas'] += ($budget->length / 10000);
                }
                else if ($priceMAterial->unit_id == 3) {
                    $arrMunicipalities[$formJson->municipality]['hectareas_impactadas'] += $budget->length;
                }

                if ($priceMAterial->unit_id == 1 & $priceMAterial->last_name == 'ribera'){
                    $arrMunicipalities[$formJson->municipality]['metros_lineales_ribera'] += $budget->length;
                }
            }
        }

        $formsTasksOpenCommunications = $communicationFormJson->where('type', 1);

        foreach ($formsTasksOpenCommunications as $formsTasksOpenCommunication) {
            $formJsonOpen = json_decode($formsTasksOpenCommunication->formjson);
            $taskOpen = $formsTasksOpenCommunication->getTaskOpen;
            if ($taskOpen->task_open_sub_type_id == 8) {
                if (!array_key_exists($formJsonOpen->municipality,$arrMunicipalities)){
                    $arrMunicipalities[$formJsonOpen->municipality] = $arrDataBase;
                }

                $arrMunicipalities[$formJsonOpen->municipality]['encuentro_actores'] += 1;
            }
        }
        return $arrMunicipalities;
    }
    private function getUserByRol($users, $idRol){
        $UsersGuardaCuenca = $users->where('role_id',$idRol);
        return $UsersGuardaCuenca;
    }
    private function getTotalHectareasConservadas($budgets){
        $totalHectareasConservadas = 0;
        foreach ($budgets as $budget) {
            $priceMaterial = $budget->actionsMaterials->budgetPriceMaterial;
            if ($priceMaterial->unit_id == 2){
                $totalHectareasConservadas += ($budget->length) / 10000;
            }
            else if ($priceMaterial->unit_id == 3) {
                $totalHectareasConservadas += $budget->length;
            }
        }
        return $totalHectareasConservadas;
    }

    /**
     * @param $budget
     * @param $map
     * @param $area_intervention
     * @return mixed
     */
    private function totalAreasTheMap($budget, $map, $area_intervention)
    {
        if ($budget->hash_map == $map['properties']['hash']) {
            if (array_key_exists('AREA_HA', $map['properties'])) {
                $area_intervention = $area_intervention + $map['properties']['AREA_HA'];
            } elseif (array_key_exists('LONGITUD_M', $map['properties'])) {
                $area_intervention = $area_intervention + $map['properties']['LONGITUD_M'];
            }
        }
        if ($area_intervention == 0){
            $area_intervention=  $budget->length;
        }
        return $area_intervention;
    }
    /**
     * @param $budget
     * @param $map
     * @param $area_intervention
     * @return mixed
     */
    private function totalAreasTheMapExecution($budget, $map, $area_intervention)
    {
        if ($budget->hash_map == $map['properties']['hash']) {
            if (array_key_exists('AREA_HA', $map['properties'])) {
                $area_intervention = $area_intervention + $map['properties']['AREA_HA'];
            } elseif (array_key_exists('LONGITUD_M', $map['properties'])) {
                $area_intervention = $area_intervention + $map['properties']['LONGITUD_M'];
            }
        }
        if ($area_intervention == 0){
            $area_intervention =  $budget->shape_leng;
        }
        return $area_intervention;
    }

    /**
     * @param $action
     * @param $budget
     * @param $map
     * @param $area_contractor_liso
     * @param $area_contractor_pua
     * @param $area_contractor_aislamiento_plantula_liso
     * @param $area_contractor_aislamiento_plantula_pua
     * @param $area_contractor_establecimiento
     * @param $area_contractor_enriquecimiento
     * @param $area_intervention
     * @return array
     */
    private function actionBySecondBudgetContractor($action, $budget, $map, $area_contractor_liso, $area_contractor_pua, $area_contractor_aislamiento_plantula_liso, $area_contractor_aislamiento_plantula_pua, $area_contractor_establecimiento, $area_contractor_enriquecimiento, $area_intervention, $type)
    {
        switch ($action->id) {
            case 17:
            case 18:
            case 19:
            case 20:
            case 21:
            case 26:
            case 28:
            case 29:
            case 44:
            case 45:
            case 46:
                if ($type == 1){
                    $area_contractor_liso = $this->totalAreasTheMap($budget, $map, $area_contractor_liso);
                }
                if ($type == 2){
                    $area_contractor_liso = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_liso);
                }

                break;
            case 9:
            case 10:
            case 11:
            case 12:
            case 13:
            case 25:
            case 30:
                if ($type == 1){
                    $area_contractor_pua = $this->totalAreasTheMap($budget, $map, $area_contractor_pua);
                }
                if ($type == 2){
                    $area_contractor_pua = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_pua);
                }

                break;
            case 22:
            case 23:
            case 24:
                if ($type == 1){
                    $area_contractor_aislamiento_plantula_liso = $this->totalAreasTheMap($budget, $map, $area_contractor_aislamiento_plantula_liso);
                }
                if ($type == 2){
                    $area_contractor_aislamiento_plantula_liso = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_aislamiento_plantula_liso);
                }

                break;
            case 14:
            case 15:
            case 16:
                if ($type == 1){
                    $area_contractor_aislamiento_plantula_pua = $this->totalAreasTheMap($budget, $map, $area_contractor_aislamiento_plantula_pua);
                }
                if ($type == 2){
                    $area_contractor_aislamiento_plantula_pua = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_aislamiento_plantula_pua);
                }

                break;
            case 1:
            case 2:
            case 3:
            case 50:
            case 51:
            case 52:
                if ($type == 1){
                    $area_contractor_establecimiento = $this->totalAreasTheMap($budget, $map, $area_contractor_establecimiento);
                }
                if ($type == 2){
                    $area_contractor_establecimiento = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_establecimiento);
                }

                break;
            case 5:
            case 6:
            case 7:
                if ($type == 1){
                    $area_contractor_enriquecimiento = $this->totalAreasTheMap($budget, $map, $area_contractor_enriquecimiento);
                }
                if ($type == 2){
                    $area_contractor_enriquecimiento = $this->totalAreasTheMapExecution($budget, $map, $area_contractor_enriquecimiento);
                }

                break;
        }
        if ($type == 1){
            $area_intervention = $this->totalAreasTheMap($budget, $map, $area_intervention);
        }
        if ($type == 2){
            $area_intervention = $this->totalAreasTheMapExecution($budget, $map, $area_intervention);
        }
        return array($area_contractor_liso, $area_contractor_pua, $area_contractor_aislamiento_plantula_liso, $area_contractor_aislamiento_plantula_pua, $area_contractor_establecimiento, $area_contractor_enriquecimiento, $area_intervention);
    }

}
