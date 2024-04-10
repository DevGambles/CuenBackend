<?php

namespace App\Http\Controllers\General;

use App\CvProcess;
use App\CvProgram;
use App\CvProject;
use App\Http\Controllers\Controller;

class GeneralProcessController extends Controller
{

    //*** Consultar programas ***//

    public function consultPrograms()
    {

        return CvProgram::get();
    }

    //*** Consultar proyectos de cada programa ***//

    public function consultProjects($id)
    {

        $programByPrject = CvProgram::find($id);

        if (empty($programByPrject)) {
            return [
                "message" => "El programa no existe en el sistema",
                "code" => 500,
            ];
        }

        $info = [];

        foreach ($programByPrject->programByProject as $value) {

            unset($value->pivot);

            array_push($info, $value);
        }

        return $info;

    }

    //*** Consultar actividades de los proyectos ***//

    public function consultProjectsActivities($id)
    {

        $projectActivity = CvProject::find($id);

        if (empty($projectActivity)) {
            return [
                "message" => "No se encontro el proyecto solicitado",
                "code" => 200,
            ];
        }

        $info = [];

        foreach ($projectActivity->projectActities as $value) {

            unset($value->pivot);

            array_push($info, $value);
        }

        return $info;
    }

    public function ProcessBudeget($id_process)
    {

        $proce = CvProcess::find($id_process);
        /*
         * TODO: agregar los estados de finalizacion de las taeras tipo PSA
         * */
        $totalAssigned = $this->getAmountAssigned($proce);
        $totalExecute = $this->getAmountExecute($proce);

        return [
            "total_comand" => $totalAssigned,
            "total_execute" => $totalExecute,
        ];
    }
    //-- Consulta las actividades dadas en el procedimiento y trae sus contribuyentes --//
    public function getActivitiesProcess($id_process, $type)
    {
        $speciearra = array();
        $info = array();
        $process = CvProcess::find($id_process);
        foreach ($process->processByProjectByActivity as $activities) {
            $contribution_array = array();
            foreach ($activities->associatedContribution as $contribution) {
                if ($type == 2) {
                    $allSpecie = new GeneralTaskOpenController();
                    $speciearra = $allSpecie->commandActivitesContributionSpecie($contribution->project_activity_id);
                }
                if ($contribution->type == $type && $contribution->year == date('Y')) {
                    array_push($contribution_array, array(
                        'id' => $contribution->id,
                        'balance' => $contribution->balance,
                        'associated_id' => $contribution->associated_id,
                        'associated_name' => $contribution->associated->name,
                        'specie' => $speciearra,
                    ));
                }
            }
            array_push($info, array(
                'id' => $activities->id,
                'name' => $activities->name,
                'associated_contribution' => $contribution_array,
            ));
        }

        return $info;
    }

    /**
     * @param $proce
     * @return int
     */
    private function getAmountAssigned($proce)
    {
        $totalAssignedByTask = 0;
        $totalAssignedByTaskOpen = 0;

        foreach ($proce->originResource as $item) {
            $totalAssignedByTask = +$item->value;
        }

        foreach ($proce->taskOpenProcess as $taskOpenProcess) {
            foreach ($taskOpenProcess->taskOpenBudgetMany as $item) {
                $totalAssignedByTaskOpen += $item->amount;
            }
        }

        $totalAssigned = $totalAssignedByTaskOpen + $totalAssignedByTask;
        return $totalAssigned;
    }

    /**
     * @param $proce
     * @return int
     */
    private function getAmountExecute($proce)
    {
        $totalExecutedByTask = 0;
        $totalExecutedByTaskOpen = 0;

        foreach ($proce->poolByProcess as $poolByProcess) {
            if ($poolByProcess->task_open_id !== null) {
                if ($poolByProcess->openTask->task_open_sub_type_id == 5 || $poolByProcess->openTask->task_open_sub_type_id == 25 ||
                    $poolByProcess->openTask->task_open_sub_type_id == 30 || $poolByProcess->openTask->task_open_sub_type_id == 38) {
                    $totalExecutedByTaskOpen += $poolByProcess->openTaskBudget->amount;
                }
            }
            if ($poolByProcess->budget_id !== null) {
                if ($poolByProcess->contractor !== null) {
                    if ($poolByProcess->contractor->excecution->isNotEmpty()) {
                        foreach ($poolByProcess->contractor->excecution as $item) {
                            $totalExecutedByTask += $item->price_execution;
                        }
                    }
                }
            }
        }

        $totalExecuted = $totalExecutedByTaskOpen + $totalExecutedByTask;
        return $totalExecuted;
    }

}
