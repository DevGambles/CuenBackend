<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvMonitoring;
use App\CvFormMonitoring;
use App\CvFormEvaluationProvider;
use App\Http\Controllers\General\GeneralMonitoringController;
use App\CvTask;
use App\CvProcess;

class GeneralFormMonitoringController extends Controller {

    //--- Consultar informacion de los formularios de monitoreo ---//

    public function consultFormMonitoring($monitoring, $type) {

        /*
         * El filtro de los formularios siguen el siguiente orden: 
         * 
         * 1. STARD
         * 2. Tracing_predial
         * 3. Certificate maintenance vegetable
         * 
         */

        $infoMonitoring = CvMonitoring::find($monitoring);

        if (empty($infoMonitoring)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        if (count($infoMonitoring->formByMonitoring) > 0) {

            foreach ($infoMonitoring->formByMonitoring as $formMonitoring) {

                switch ($type) {
                    case 1:
                        return array("data" => $formMonitoring->form_stard);
                    case 2:
                        return array("data" => $formMonitoring->form_tracing_predial);
                    case 3:
                        return array("data" => $formMonitoring->form_certificate_maintenance_vegetable);

                    default:
                        return [
                            "message" => "No se encuentra ningun formulario para la opcion de tipo " . $type . " en monitoreo",
                            "response_code" => 200
                        ];
                }
            }
        } else {
            return [
                "message" => "El monitoreo no cuenta con registro de formularios",
                "response_code" => 200
            ];
        }
    }

    //--- Registrar formularios de un monitoreo en especifico ---//

    public function registerFormMonitoring(Request $request, $monitoring, $type) {

        /*
         * El filtro de los formularios siguen el siguiente orden: 
         * 
         * 1. STARD
         * 2. Tracing_predial
         * 3. Certificate maintenance vegetable
         * 
         */

        $monitoringInfo = CvMonitoring::find($monitoring);

        if (empty($monitoringInfo)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Filtrar el tipo de monitoreo para guardar en el sistema ---//

        if (count($monitoringInfo->formByMonitoring) > 0) {
            $formMonitoring = CvFormMonitoring::where("monitoring_id", $monitoring)->first();
        } else {
            $formMonitoring = new CvFormMonitoring();
        }

        switch ($type) {

            case 1:
                $formMonitoring->form_stard = json_encode($request->all());
                break;
            case 2:
                $formMonitoring->form_tracing_predial = json_encode($request->all());
                break;
            case 3:
                $formMonitoring->form_certificate_maintenance_vegetable = json_encode($request->all());
                break;

            default:

                return [
                    "message" => "No se encuentra ningun formulario para la opcion de tipo " . $type . " en monitoreo",
                    "response_code" => 200
                ];
        }

        $formMonitoring->monitoring_id = $monitoring;

        if ($formMonitoring->save()) {

            //--- Guardar historial ---//

            $generalMonitoring = new GeneralMonitoringController();
            $backupMonitoring = $generalMonitoring->backFlowMonitoring($monitoringInfo->id, $this->userLoggedInId(), null);

            if ($backupMonitoring == true) {
                return [
                    "message" => "Registro exitoso",
                    "response_code" => 200
                ];
            }
        }
    }

    //--- Registrar formulario de proveedores ---//

    public function registerFormProvider(Request $request, $monitoring) {

        $monitoringInfo = CvMonitoring::find($monitoring);

        if (empty($monitoringInfo)) {
            return [
                "message" => "El monitoreo no existe en el sistema",
                "response_code" => 200
            ];
        }

        $formProvider = new CvFormEvaluationProvider();

        $infoRequest = json_decode(json_encode($request->all()), true);

        if (!empty($infoRequest)) {

            $formProvider->form = json_encode($request->all());
            $formProvider->contract_number = $infoRequest["contractNumber"];
            $formProvider->provider_name = $infoRequest["providerName"];
            $formProvider->nit_provider = $infoRequest["nitProvider"];
            $formProvider->evaluation_period = $infoRequest["evaluationPeriod"];
            $formProvider->score = $infoRequest["score"];
            $formProvider->is_approved = $infoRequest["isApproved"];
            $formProvider->category = $infoRequest["category"];
            $formProvider->comments = $infoRequest["comments"];
            $formProvider->monitoring_id = $monitoring;

            if ($formProvider->save()) {

                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            }
        }
    }

    /*
     * Consultar información del formulario STARD de un procedimiento 
     */

    public function consultInformationFormStardProcess($id) {

        $proces = CvTask::find($id);
        $monitoring = $proces->process[0]->find($proces->process[0]['id'])->monitoring;

        if ($monitoring == null){
            return [
                "message" => "No hay monitoreo",
                "code" => 500
            ];
        }

        $formMonitring = $monitoring->find($monitoring->id)->formByMonitoring;
        $startform = $formMonitring->where('monitoring_id', $monitoring->id)->first();
        if (isset($startform)) {

            $generalMonitoring = new GeneralMonitoringController();

            $generalMonitoring->show($monitoring->id);

            return array_add($generalMonitoring->show($monitoring->id), "form_stard", $startform->form_stard);
        } else {
            return [
                "message" => "No hay Formulario que mostrar",
                "code" => 500
            ];
        }
    }

}
