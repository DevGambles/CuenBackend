<?php

namespace App\Http\Controllers;

use App\CvActions;
use App\CvAssociated;
use App\CvAssociatedContribution;
use App\CvBackupContribution;
use App\CvBudget;
use App\CvComandExcel;
use App\CvContributionPerShare;
use App\CvContributionSpecies;
use App\CvGoalsForContribution;
use App\CvProgram;
use App\CvProgramByProject;
use App\CvProject;
use App\CvProjectActivity;
use App\CvProjectByActivity;
use App\CvTask;
use App\CvTaskOpenBudget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CommandAndController extends Controller
{

    public function allAssociated()
    {
        return (CvAssociated::orderBy('created_at')->get());
    }

    public function insertInversionAssociated(Request $date)
    {

        $carbon = new Carbon();
        switch ($date->type) {
            //si el tipo es 1 inserta aporte dinero
            case 1:
                //solo se puede ingresar un aporte dinero por asociado por actividad por año
                $validate = CvAssociatedContribution::where('year', Carbon::parse($carbon->now())->year)->where('type', $date->type)->where('associated_id', $date->associated_id)->where('project_activity_id', $date->project_activity_id);

                if ($validate->exists()) {
                    return [
                        "message" => "Ya hay un registro existente",
                        "response_code" => 500,
                    ];
                }
                $budget = $date->budget;
                $especie = 0;
                $other = "NeW Contribution";
                $type = $date->type;
                return $this->insertContribution($date, $budget, $especie, $other, $type);

            //si el tipo es 2 inserta aporte es en especie
            case 2:
                //solo se puede registrar un aporte especie por asociado por actividad por año sin embargo la tabla contributio especie es escalable
                $validate = CvAssociatedContribution::where('year', Carbon::parse($carbon->now())->year)->where('type', $date->type)->where('associated_id', $date->associated_id)->where('project_activity_id', $date->project_activity_id);

                if ($validate->exists()) {
                    return [
                        "message" => "Ya hay un registro existente",
                        "response_code" => 500,
                    ];
                }
                $budget = 0;
                $especie = $date->budget_species;
                $other = "NeW Species";
                $type = $date->type;
                return $this->insertContribution($date, $budget, $especie, $other, $type);

            case 3:
                $responsone = 500;
                $responstwo = 500;
                $texto1 = "Se a registrado un nuevo aporte";
                $respons = 200;
                //solo se puede registrar un aporte especie por asociado por actividad por año sin embargo la tabla contributio especie es escalable
                $onevalidate = CvAssociatedContribution::where('year', Carbon::parse($carbon->now())->year)->where('type', 1)->where('associated_id', $date->associated_id)->where('project_activity_id', $date->project_activity_id);
                //APORTE CON DINERO
                if ($onevalidate->exists()) {
                    $responsone == 500;
                } else {
                    $budget = $date->budget;
                    $especie = 0;
                    $other = "NeW Contribution";
                    $type = 1;
                    $responsone = $this->insertContribution($date, $budget, $especie, $other, $type)['response_code'];
                }

                //solo se puede registrar un aporte especie por asociado por actividad por año sin embargo la tabla contributio especie es escalable
                $twovalidate = CvAssociatedContribution::where('year', Carbon::parse($carbon->now())->year)->where('type', 2)->where('associated_id', $date->associated_id)->where('project_activity_id', $date->project_activity_id);
                //APORTE EN ESPECIE
                if ($twovalidate->exists()) {
                    $responstwo = 500;
                } else {
                    $budget = 0;
                    $especie = $date->budget_species;
                    $other = "NeW Species";
                    $type = 2;
                    $responstwo = $this->insertContribution($date, $budget, $especie, $other, $type)['response_code'];
                }

                if ($responsone == 500 || $responstwo == 500) {

                    $texto1 = "Algun aporte no se almaceno correctamente Dinero= " . $responsone . " Especie= " . $responstwo;
                    $respons = 500;

                    return [
                        "message" => $texto1,
                        "response_code" => $respons,
                    ];
                } else {
                    return [
                        "message" => $texto1,
                        "response_code" => $respons,
                    ];
                }
        }
    }

//Todas las aportes por asociado
    public function allBudget()
    {
        $carbon = new Carbon();

        $budgets = CvAssociatedContribution::where('year', Carbon::parse($carbon->now())->year)->get();

        $info = array();

        foreach ($budgets as $value) {

            $comitend = 0;
            $associated_name = CvAssociated::find($value->associated_id);
            $activity_name = CvProjectActivity::find($value->project_activity_id);
            $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $project = CvProject::find($projbyacti->project_id);
            $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $program = CvProgram::find($programbyproject->program_id);
            $pershare = CvContributionPerShare::where('associated_id', $associated_name->id)->get();

            /*foreach ($pershare as $pershareValue) {
                $comitend = $comitend + $pershareValue->budget->value;
            }

            if ($value->budgetTaskOpen->isNotEmpty()){
                foreach ($value->budgetTaskOpen as $budgetOpen) {
                    $comitend += $budgetOpen->amount;
                }
            }*/

            array_push($info, array(
                "id" => $value->id,
                "year" => $value->year,
                "budget" => $value->inversion,
                "budget_species" => $value->inversion_species,
                "associated" => $associated_name->name,
                "type" => $value->type,
                "activity" => $activity_name->name,
                "project" => $project->name,
                "program" => $program->name,
                "paid_budget" => $value->paid,
                "committed_budget" => $value->committed,
            ));
        }

        return $info;
    }

    public function yearAllBudget($yeard)
    {
        $budgets = CvAssociatedContribution::where('year', $yeard)->get();
        $info = array();

        foreach ($budgets as $value) {

            $comitend = 0;
            $associated_name = CvAssociated::find($value->associated_id);
            $activity_name = CvProjectActivity::find($value->project_activity_id);
            $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $project = CvProject::find($projbyacti->project_id);
            $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $program = CvProgram::find($programbyproject->program_id);
            $pershare = CvContributionPerShare::where('associated_id', $associated_name->id)->get();

            foreach ($pershare as $pershareValue) {
                $comitend = $comitend + $pershareValue->budget->value;
            }

            array_push($info, array(
                "id" => $value->id,
                "year" => $value->year,
                "budget" => $value->inversion,
                "budget_species" => $value->inversion_species,
                "associated" => $associated_name->name,
                "type" => $value->type,
                "activity" => $activity_name->name,
                "project" => $project->name,
                "program" => $program->name,
                "paid_budget" => $value->paid,
                "committed_budget" => $comitend,
            ));
        }

        return $info;
    }

    public function detailBudget($id_investment)
    {
        $budgets = CvAssociatedContribution::find($id_investment);
        $info = array();
        $spec = array();

        $associated_name = CvAssociated::find($budgets->associated_id);
        $activity_name = CvProjectActivity::find($budgets->project_activity_id);
        $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
        $project = CvProject::find($projbyacti->project_id);
        $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
        $program = CvProgram::find($programbyproject->program_id);
        foreach ($budgets->species as $value) {

            array_push($spec, array(
                "id" => $value->id,
                "quantity" => $value->quantity,
                "price_unit" => $value->price_unit,
                "description" => $value->description,
            ));
        }
        array_push($info, array(
            "id" => $budgets->id,
            "budget" => $budgets->inversion,
            "budget_species" => $budgets->inversion_species,
            "associated" => $associated_name->name,
            "activity" => $activity_name->name,
            "type" => $budgets->type,
            "project" => $project->name,
            "program" => $program->name,
            "paid_budget" => $budgets->paid,
            "committed_budget" => $budgets->committed,
            "species_contribution" => $spec,
        ));

        return (!empty($info)) ? $info[0] : $info;
    }

    public function updateInversionAssociated(Request $date)
    {
        $info = array();
        $Contribution = CvAssociatedContribution::find($date->id);
        if ($Contribution) {
            switch ($date->type) {
                case 1:
                    if ($Contribution->committed != null && $Contribution->committed != 0) {
                        $Contribution->committed = $Contribution->committed - $date->paid_budget;
                        if ($Contribution->committed <= 0) {
                            $Contribution->committed = 0;
                            $Contribution->committed_balance = 0;
                        }
                    }
                    $passcontri = $Contribution->inversion;
                    $Contribution->balance = $Contribution->inversion - $date->paid_budget;
                    if ($Contribution->balance < 0) {
                        return [
                            "message" => "No hay presupuesto disponible para pagar",
                            "response_code" => 500,
                        ];
                    }
                    $Contribution->inversion = $date->budget;
                    $Contribution->paid = $date->paid_budget;
                    if ($Contribution->save()) {
                        array_push($info, array(
                            "inversion_to" => 0,
                            "inversion_end" => $Contribution->project_activity_id,
                            "budget" => $Contribution->inversion,
                            "budget_species" => $Contribution->inversion_species,
                            "year" => $Contribution->year,
                            "type" => $Contribution->type,
                            "other" => "Update contribution from" . $passcontri . " to " . $date->budget,
                            "Contribution_id" => $Contribution->id,
                        ));

                        $this->history($info);
                        return [
                            "message" => "Inversión actualizada",
                            "response_code" => 200,
                        ];
                    }
                case 2:
                    if ($Contribution->committed != null && $Contribution->committed != 0) {
                        $Contribution->committed = $Contribution->committed - $date->paid_budget;
                        if ($Contribution->committed <= 0) {
                            $Contribution->committed = 0;
                            $Contribution->committed_balance = 0;
                        }
                    }
                    $passcontri = $Contribution->inversion_species;
                    $Contribution->balance = $Contribution->inversion_species - $date->paid_budget;
                    if ($Contribution->balance < 0) {
                        return [
                            "message" => "No hay presupuesto disponible para pagar",
                            "response_code" => 500,
                        ];
                    }
                    $Contribution->inversion_species = $date->budget_species;
                    $Contribution->paid = $date->paid_budget;

                    if ($Contribution->save()) {
                        $this->controlSpecie($date->id, $date->species_contribution);

                        array_push($info, array(
                            "inversion_to" => 0,
                            "inversion_end" => $Contribution->project_activity_id,
                            "budget" => $Contribution->inversion,
                            "budget_species" => $Contribution->inversion_species,
                            "year" => $Contribution->year,
                            "type" => $Contribution->type,
                            "other" => "Update contribution from" . $passcontri . " to " . $date->budget,
                            "Contribution_id" => $Contribution->id,
                        ));

                        $this->history($info);
                        return [
                            "message" => "Inversión por especie actualizada",
                            "response_code" => 200,
                        ];
                    }
            }
        } else {
            return [
                "message" => "No existe la inversión consultada cree una",
                "response_code" => 500,
            ];
        }
    }

    public function transalateInversionAssociated(Request $date)
    {

        $info = array();
        $tras = array();
        $carbon = new Carbon();
        //consulta inversion original
        $NCActual = CvAssociatedContribution::find($date->id);

        //valida inversiones de traslado existentes
        $validateBudget = $NCActual->inversion;

        $validate = CvAssociatedContribution::where('associated_id', $NCActual->associated_id)
            ->where('project_activity_id', $date->activity_traslate)
            ->where('type', 1)
            ->count();

        if ($validate == 0) {

            $NCActual->inversion = $validateBudget - $date->budget_traslate;

            if ($NCActual->inversion <= 0) {
                return [
                    "message" => "El valor es mayor a la inversion original",
                    "response_code" => 500,
                ];
            }
            //Historial

            array_push($info, array(
                "inversion_to" => 0,
                "inversion_end" => $date->activity_traslate,
                "budget" => $validateBudget,
                "budget_species" => $NCActual->inversion_species,
                "year" => $NCActual->year,
                "type" => $NCActual->type,
                "other" => "edit origin contribution  from activity" . $NCActual->project_activity_id . " to " . $date->activity_traslate,
                "Contribution_id" => $NCActual->id,
            ));
            $this->history($info);
            if ($NCActual->save()) {

                //crea nueva inversion
                $contribution = new CvAssociatedContribution();
                $contribution->inversion = $date->budget_traslate;
                $contribution->inversion_species = 0;
                $contribution->associated_id = $NCActual->associated_id;
                $contribution->type = 1;
                $contribution->project_activity_id = $date->activity_traslate;
                $contribution->year = $date->year;
                if ($contribution->save()) {
                    //Historial
                    array_push($tras, array(
                        "inversion_to" => $NCActual->project_activity_id,
                        "inversion_end" => $date->activity_traslate,
                        "budget" => $contribution->inversion,
                        "budget_species" => $NCActual->inversion_species,
                        "year" => $NCActual->year,
                        "type" => $NCActual->type,
                        "other" => "traslate origin contribution   from activity" . $NCActual->project_activity_id . " to " . $date->activity_traslate,
                        "Contribution_id" => $contribution->id,
                    ));
                    $this->history($tras);
                }
            }
            return [
                "message" => "Se creado un traslado",
                "response_code" => 200,
            ];
        } else {

            $validate = CvAssociatedContribution::where('associated_id', $NCActual->associated_id)->where('project_activity_id', $date->activity_traslate)->where('type', 1);

            $NCActual->inversion = $validateBudget - $date->budget_traslate;

            if ($NCActual->save()) {

                //Historial

                array_push($info, array(
                    "inversion_to" => 0,
                    "inversion_end" => $NCActual->project_activity_id,
                    "budget" => $validateBudget,
                    "budget_species" => $NCActual->inversion_species,
                    "year" => $NCActual->year,
                    "type" => $NCActual->type,
                    "other" => "edit origin contribution  from activity" . $NCActual->project_activity_id . " to " . $date->activity_traslate,
                    "Contribution_id" => $NCActual->id,
                ));
                $this->history($info);
                $contribution = CvAssociatedContribution::find($validate->id);
                $contribution->inversion = $contribution->inversion + $date->budget_traslate;
                $contribution->project_activity_id = $date->activity_traslate;
                if ($contribution->save()) {
                    //Historial
                    array_push($tras, array(
                        "inversion_to" => $NCActual->project_activity_id,
                        "inversion_end" => $date->activity_traslate,
                        "budget" => $contribution->inversion,
                        "budget_species" => $NCActual->inversion_species,
                        "year" => $NCActual->year,
                        "type" => $NCActual->type,
                        "other" => "traslate origin contribution   from activity" . $NCActual->project_activity_id . " to " . $date->activity_traslate,
                        "Contribution_id" => $contribution->id,
                    ));
                    $this->history($tras);
                }
            }
            return [
                "message" => "Se traslado a  una inversion existente",
                "response_code" => 200,
            ];
        }
    }

//FUNCION DE FILTROS SUPER DELICADA
    public function filterAssociatedActivity($date, $id, $year)
    {

        $info = array();
        $bjet = array();
        $tinversion = 0;
        $tpaid = 0;
        $tcommitted = 0;
        $tgoal = 0;
        $obje_id = 0;
        $tbudget = 0;
        $totatalesbudget = 0;

        if ($date == 1) {
            //asociado
            $asociate = CvAssociated::find($id);

            if ($year != 0) {
                $contributionAsociated = CvAssociatedContribution::where('associated_id', $asociate->id)->where('year', $year)->get();
            }
            else {
                $contributionAsociated = CvAssociatedContribution::where('associated_id', $asociate->id)->get();
            }

            foreach ($contributionAsociated as $object) {

                if ($obje_id != $object->id) {
                    $tgoal = CvGoalsForContribution::where('contributions_id', $object->id)->count() + $tgoal;
                    $obje_id = $object->id;
                }
                //budget
                $perShares = CvContributionPerShare::where('associated_id', $object->associated_id)->get();
                $tbudget = 0;

                //editar
                $contribuAsociated = CvAssociatedContribution::find($object->id);

                //budget
                $tinversion = $object->inversion + $tinversion;
                $tpaid = $object->paid + $tpaid;
                $tcommitted = $contribuAsociated->committed + $tcommitted;
                $associated_name = CvAssociated::find($object->associated_id);
                $activity_name = CvProjectActivity::find($object->project_activity_id);
                $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                $project = CvProject::find($projbyacti->project_id);
                $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                $program = CvProgram::find($programbyproject->program_id);

                //array
                array_push($bjet, array(
                    "id" => $contribuAsociated->id,
                    "budget" => $contribuAsociated->inversion,
                    "associated" => $associated_name->name,
                    "activity" => $activity_name->name,
                    "project" => $project->name,
                    "program" => $program->name,
                    "paid_budget" => $contribuAsociated->paid,
                    "committed_budget" => $contribuAsociated->committed,
                    "year" => $object->year,
                    'type' => $object->type
                ));
            }
            $newcontributionAsociated = $contributionAsociated;
            foreach ($newcontributionAsociated as $value) {
                $totatalesbudget = $value->committed + $totatalesbudget;
            }
            array_push($info, array(
                "totalities" => array(
                    "total_budget" => $tinversion,
                    "total_paid_budget" => $tpaid,
                    "total_committed_budget" => $tcommitted,
                    "total_goal" => $tgoal,
                    "total_committed_budget" => $totatalesbudget,
                ),
                "detail" => $bjet,
            ));
            //EDICIONES
        } elseif ($date == 2) {
            //actividad
            $activity = CvProjectActivity::find($id);
            if ($year != 0) {
                $contributionAsociated = CvAssociatedContribution::where('project_activity_id', $activity->id)->where('year', $year)->get();
            } else {
                $contributionAsociated = CvAssociatedContribution::where('project_activity_id', $activity->id)->get();
            }
            foreach ($contributionAsociated as $object) {
                $this->calcule($object->associated_id);
                if ($obje_id != $object->id) {

                    $tgoal = CvGoalsForContribution::where('contributions_id', $object->id)->count() + $tgoal;
                    $obje_id = $object->id;
                }
                //budget
                $perShares = CvContributionPerShare::where('associated_id', $object->associated_id)->get();

                $tinversion = $object->inversion + $tinversion;
                $tpaid = $object->paid + $tpaid;
                $tcommitted = $object->committed + $tcommitted;
                $associated_name = CvAssociated::find($object->associated_id);
                $activity_name = CvProjectActivity::find($object->project_activity_id);
                $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                $project = CvProject::find($projbyacti->project_id);
                $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                $program = CvProgram::find($programbyproject->program_id);
                array_push($bjet, array(
                    "id" => $object->id,
                    "budget" => $object->inversion,
                    "associated" => $associated_name->name,
                    "activity" => $activity_name->name,
                    "project" => $project->name,
                    "program" => $program->name,
                    "paid_budget" => $object->paid,
                    "committed_budget" => $object->committed,
                    'year' => $year,
                    'type' => $object->type
                ));
            }
            $newcontributionActif = $contributionAsociated;
            foreach ($newcontributionActif as $value) {
                $totatalesbudget = $value->committed + $totatalesbudget;
            }
            array_push($info, array(
                "totalities" => array(
                    "total_budget" => $tinversion,
                    "total_paid_budget" => $tpaid,
                    "total_committed_budget" => $totatalesbudget,
                    "total_goal" => $tgoal
                ),
                "detail" => $bjet,
            ));
        }

        /**
         * Consulta solo por año
         */

        elseif ($date == 3) {

            /**
             * Actividad y asociados juntos en el reporte
             */

            $activities = CvProjectActivity::get();
            $asociates = CvAssociated::get();

            /**
             * Arreglo para actividades y asociados
             */
            foreach ($activities as $activity) {

                $contributionAsociatedes = CvAssociatedContribution::where('project_activity_id', $activity->id)->where('year', $year)->get();

                foreach ($contributionAsociatedes as $object) {

                    $this->calcule($object->associated_id);

                    if ($obje_id != $object->id) {

                        $tgoal = CvGoalsForContribution::where('contributions_id', $object->id)->count() + $tgoal;
                        $obje_id = $object->id;
                    }
                    //budget
                    $perShares = CvContributionPerShare::where('associated_id', $object->associated_id)->get();

                    $tinversion = $object->inversion + $tinversion;
                    $tpaid = $object->paid + $tpaid;
                    $tcommitted = $object->committed + $tcommitted;
                    $associated_name = CvAssociated::find($object->associated_id);
                    $activity_name = CvProjectActivity::find($object->project_activity_id);
                    $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                    $project = CvProject::find($projbyacti->project_id);
                    $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
                    $program = CvProgram::find($programbyproject->program_id);
                    array_push($bjet, array(
                        "id" => $object->id,
                        "budget" => $object->inversion,
                        "associated" => $associated_name->name,
                        "activity" => $activity_name->name,
                        "project" => $project->name,
                        "program" => $program->name,
                        "paid_budget" => $object->paid,
                        "committed_budget" => $object->committed,
                        "year" => $object->year,
                        "type" => $object->type,
                        "actividad" => true
                    ));
                }

                $newcontributionActif = $contributionAsociatedes;
                foreach ($newcontributionActif as $value) {
                    $totatalesbudget = $value->committed + $totatalesbudget;
                }
            }

            array_push($info, array(
                "totalities" => array(
                    "total_budget" => $tinversion,
                    "total_paid_budget" => $tpaid,
                    "total_committed_budget" => $totatalesbudget,
                    "total_goal" => $tgoal,
                    "year" => $year,
                ),
                "detail" => $bjet,
            ));

        }
        return $info[0];
    }

    //END FUNCION DE FILTROS SUPER DELICADA
    //ACCIONES
    //array de acciones por asociado insertar en tabla  contribution_per_shares

    public function insertActionBudgetAssociated(Request $actionAssociated)
    {

        for ($index = 0; $index < count($actionAssociated->all()); $index++) {
            $pershare = CvContributionPerShare::where('task_id', $actionAssociated[$index]['id_task']);
            if ($pershare->exists()) {
                $pershare->delete();
            }
        }

        for ($index = 0; $index < count($actionAssociated->all()); $index++) {

            $insert = new CvContributionPerShare();
            $insert->associated_id = $actionAssociated[$index]['id_associated'];
            $insert->budget_id = $actionAssociated[$index]['id_budget'];
            $insert->task_id = $actionAssociated[$index]['id_task'];
            $insert->save();
        }
        return [
            "message" => "Almacenado en presupuesto por accion",
            "response_code" => 200,
        ];
    }

    //valida que se insertaron los campos entra id task
    public function validateTaskAssociatedBudget($id_task)
    {
        $estado = "False";
        $pershare = CvContributionPerShare::where('task_id', $id_task);
        if ($pershare->exists()) {
            if (!empty($pershare->first()->budget_id) && !empty($pershare->first()->associated_id)) {
                $estade = "True"; //true
            } else {
                $estade = "False"; //false
            }

            return $estade;
        } else {
            return $estado;
        }
    }

    //Analisis de comprometido y pagado por asociado por actividad
    public function commitmentBudgetAnalyze($id)
    {
        $info = array();
        $tltl = array();
        $tbudget = 0;
        $contribution = CvAssociatedContribution::find($id);
        $perShares = CvContributionPerShare::where('associated_id', $contribution->associated_id)->get();

        foreach ($perShares as $per) {
            //Actividad
            $task = CvTask::find($per->task_id);
            $activity = CvProjectActivity::find($task->taskType->project_activity_id);
            if ($activity->id == $contribution->project_activity_id) {

                //presupuesto por Accion
                $budget = CvBudget::find($per->budget_id);
                $action = CvActions::find($budget->actionsMaterials->action_id);
                //Calculo
                $tbudget = $budget->value + $tbudget;
                array_push($info, array(
                    "activity" => $activity->name,
                    "action" => $action->name,
                    "budget" => $budget->value,
                ));
            }
        }

        array_push($tltl, array(
            "totalities" => array(
                "total_committed" => $tbudget,
                "total_paid" => $contribution->paid,
            ),
            "detail" => $info,
        ));

        return $tltl;
    }

    public function shearhActionAsociated($id_task)
    {
        $info = array();
        $persahe = CvContributionPerShare::where('task_id', $id_task)->get();
        foreach ($persahe as $value) {
            array_push($info, array(
                "associated_id" => $value->associated_id,
                "budget_id" => $value->budget_id,
            ));
        }
        return $info;
    }

//elimina accion contribuyente
    public function actionDelete(Request $date)
    {

        $detailAction = CvContributionPerShare::where('id', $date->id);
        if ($detailAction->delete()) {
            return [
                "message" => "Accion Eliminada",
                "response_code" => 200,
            ];
        } else {
            return [
                "message" => "No se elimino la accion",
                "response_code" => 500,
            ];
        }
    }

    //END ACCIONES
    //CRUD METAS
    public function goalInsert(Request $goals)
    {
        $meta = new CvGoalsForContribution();
        $meta->unit = $goals->unit;
        $meta->description = $goals->description;
        $meta->quantity = $goals->quantity;
        $meta->contributions_id = $goals->contributions_id;
        if ($meta->save()) {
            return [
                "message" => "Meta almacenada",
                "response_code" => 200,
            ];
        } else {
            return [
                "message" => "No fue posible almacenar la Meta",
                "response_code" => 500,
            ];
        }
    }

    public function goalReadAll()
    {
        return (CvGoalsForContribution::orderBy('created_at')->get());
    }

    public function goalReadDetail($id_goal)
    {
        $detailGoal = CvGoalsForContribution::find($id_goal);
        $info = array();
        if ($detailGoal) {
            $associated_name = CvAssociated::find($detailGoal->Contribution->associated_id);
            $activity_name = CvProjectActivity::find($detailGoal->Contribution->project_activity_id);
            $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $project = CvProject::find($projbyacti->project_id);
            $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $program = CvProgram::find($programbyproject->program_id);

            array_push($info, array(
                "id" => $detailGoal->id,
                "unit" => $detailGoal->unit,
                "description" => $detailGoal->description,
                "quantity" => $detailGoal->quantity,
                "created_at" => $detailGoal->created_at->format('Y-m-d H:i:s'),
                "updated_at" => $detailGoal->updated_at->format('Y-m-d H:i:s'),
                "AporteList" => array(
                    "id" => $detailGoal->Contribution->id,
                    "budget" => $detailGoal->Contribution->inversion,
                    "associated" => $associated_name->name,
                    "activity" => $activity_name->name,
                    "project" => $project->name,
                    "program" => $program->name,
                    "paid_budget" => $detailGoal->Contribution->paid,
                    "committed_budget" => $detailGoal->Contribution->committed,
                ),
            ));
            return (!empty($info)) ? $info[0] : $info;
        } else {
            return [
                "message" => "La meta No existe",
                "response_code" => 500,
            ];
        }
    }

    public function goalDelet(Request $date)
    {
        $detailGoal = CvGoalsForContribution::where('id', $date->id);
        if ($detailGoal->delete()) {
            return [
                "message" => "Meta Eliminada",
                "response_code" => 200,
            ];
        } else {
            return [
                "message" => "No se elimino la meta",
                "response_code" => 500,
            ];
        }
    }

    //END CRUD METAS
    //Reporte de comando y control
    public function ReportComandAndController()
    {

        $program = array();
        $project = array();
        $activities = array();
        $contribution = array();
        $associated = array();
        $test = array();
        $allprograms = CvProgram::all();
        //Insert program
        foreach ($allprograms as $detal_program) {
            foreach ($detal_program->programByProject as $detalproject) {
                foreach ($detalproject->projectActities as $detalactivities) {
                    $cvcotribution = CvAssociatedContribution::where('project_activity_id', $detalactivities->id);
                    if ($cvcotribution->exists()) {
                        if ($this->insertDuplic($program, $detal_program->id) == false) {
                            array_push($program, array(
                                "program_name" => $detal_program->name,
                                "id" => $detal_program->id,
                            ));
                        }
                    }
                }
            }
        }
        //Insert Project
        for ($i = 0; $i < count($program); $i++) {
            foreach (CvProgram::find($program[$i]['id'])->programByProject as $detalproject) {
                foreach ($detalproject->projectActities as $detalactivities) {
                    $cvcotribution = CvAssociatedContribution::where('project_activity_id', $detalactivities->id);
                    if ($cvcotribution->exists()) {
                        if ($this->insertDuplic($project, $detal_program->id) == false) {
                            array_push($project, array(
                                "project_name" => $detalproject->name,
                                "id" => $detalproject->id,
                                'program' => $program[$i]['id'],
                            ));
                        }
                    }
                }
            }
        }
        //Insert Activites
        for ($i = 0; $i < count($project); $i++) {
            foreach (CvProject::find($project[$i]['id'])->projectActities as $detalactivities) {
                $cvcotribution = CvAssociatedContribution::where('project_activity_id', $detalactivities->id);
                if ($cvcotribution->exists()) {
                    if ($this->insertDuplic($activities, $detalactivities->id) == false) {

                        array_push($activities, array(
                            "activite_name" => $detalactivities->name,
                            "id" => $detalactivities->id,
                            'project' => $project[$i]['id'],
                        ));
                    }
                }
            }
        }
        //insert associative
        foreach ($activities as $detalactivities) {
            $inversion = 0;
            $inversionSpecies = 0;
            $paid = 0;
            $commited = 0;
            $contri = CvAssociatedContribution::where('project_activity_id', $detalactivities["id"]);
            if ($contri->exists()) {
                foreach ($contri->get() as $detalcontribution) {

                    array_push($associated, array(
                        "name" => $detalcontribution->thisisassociate->name,
                        "id" => $detalcontribution->thisisassociate->id,
                        'activite' => $detalcontribution->project_activity_id,
                        'associa_contri' => $detalcontribution->id,
                    ));
                }
            }
        }

        //insert contributions
        foreach ($associated as $detalactivities) {
            $inversion = 0;
            $inversionSpecies = 0;
            $paid = 0;
            $commited = 0;

            $contri = CvAssociatedContribution::where('project_activity_id', $detalactivities["activite"])->where('associated_id', $detalactivities['id']);
            if ($contri->exists()) {
                foreach ($contri->get() as $detalcontribution) {
                    $inversion = $inversion + $detalcontribution->inversion;
                    $inversionSpecies = $inversionSpecies + $detalcontribution->inversion_species;
                    $paid = $paid + $detalcontribution->paid;
                    $commited = $commited + $detalcontribution->committed;
                }

                array_push($contribution, array(
                    "inversion" => $inversion,
                    "inversionspecies" => $inversionSpecies,
                    'paid' => $paid,
                    'commited' => $commited,
                    'activite' => $detalactivities['activite'],
                    'associative' => $detalactivities['id'],
                    'associcontri' => $detalactivities['associa_contri'],
                ));
            }
        }

        //Insert array associates in activities
        $associated = $this->insertArrayProgram($associated, $contribution, 'associcontri', 'contributions', 'associa_contri');
        //Insert array associates in activities
        $activities = $this->insertArrayProgram($activities, $associated, 'activite', 'associates', 'id');
        //Insert array activities in project
        $project = $this->insertArrayProgram($project, $activities, 'project', 'activities', 'id');
        //Insert array project in program
        $program = $this->insertArrayProgram($program, $project, 'program', 'projects', 'id');

        $this->deleteExelTable();
        $this->insertExeltable($program);
        $this->excelPrint();
        return $program;
    }

    //Reporte de comando y control
    public function ReportComandAndControllerDownload($year)
    {
        $carbon = new Carbon();

        $budgets = CvAssociatedContribution::where('year', $year)->get();
        $info = array();
        $i = 1;

        $this->deleteExelTable();
        foreach ($budgets as $value) {
            $exel = new CvComandExcel();

            $comitend = 0;
            $associated_name = CvAssociated::find($value->associated_id);
            $activity_name = CvProjectActivity::find($value->project_activity_id);
            $projbyacti = CvProjectByActivity::where('activity_id', $activity_name->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $project = CvProject::find($projbyacti->project_id);
            $programbyproject = CvProgramByProject::where('project_id', $project->id)->first(); //PIVOT ESTABLECER RELACION EN CASO DE MUCHOS A MUCHOS
            $program = CvProgram::find($programbyproject->program_id);
            $pershare = CvContributionPerShare::where('associated_id', $associated_name->id)->get();

            foreach ($pershare as $pershareValue) {
                $comitend = $comitend + $pershareValue->budget->value;
            }

            $countGoal = 0;
            $goal = CvGoalsForContribution::where('contributions_id', $associated_name->associa_contri);
            if ($goal->exists()) {
                $countGoal = count($goal->get());
            }


            $exel = new CvComandExcel();
            $exel->id = $i;
            $exel->programas = $program->name;
            $exel->projectos = $project->name;
            $exel->actividades = $activity_name->name;
            $exel->metas = $countGoal;
            $exel->asociados = $associated_name->name;
            $exel->inversion = $value->inversion_origin;
            $exel->especie = $value->inversion_species;
            $exel->pagado = $value->paid;
            $exel->comprometido = $value->committed;
            $exel->save();
            $i = $i +1;

        }

        $this->excelPrint();
        return $program;
    }

    //***FUNCIONES GLOBALES***

    private function deleteExelTable()
    {

        $exel = CvComandExcel::all();
        foreach ($exel as $value) {

            $value->delete();
        }
    }

    private function insertExeltable($param)
    {
        $i = 1;
        $final_inversion = 0;
        $final_pagado = 0;
        $final_comprometido = 0;
        $final_metas = 0;

        foreach ($param as $program) {
            if (array_key_exists('projects', $program)) {
                foreach ($program['projects'] as $project) {
                    $total_inversion = 0;
                    $total_inversion_especie = 0;
                    $total_pagado = 0;
                    $total_comprometido = 0;
                    $total_metas = 0;
                    foreach ($project['activities'] as $activity) {
                        $activiy_inversion = 0;
                        $activiy_inversion_especie = 0;
                        $activiy_pagado = 0;
                        $activiy_comprometido = 0;
                        $activiy_metas = 0;
                        foreach ($activity['associates'] as $associated) {
                            $countGoal = 0;
                            $goal = CvGoalsForContribution::where('contributions_id', $associated['associa_contri']);
                            if ($goal->exists()) {
                                $countGoal = count($goal->get());
                            }
                            //total por proyecto
                            $total_inversion = $total_inversion + $associated['contributions'][0]['inversion'];
                            $total_inversion_especie = $total_inversion_especie + $associated['contributions'][0]['inversionspecies'];
                            $total_pagado = $total_pagado + $associated['contributions'][0]['paid'];
                            $total_comprometido = $total_comprometido + $associated['contributions'][0]['commited'];
                            $total_metas = $total_metas + $countGoal;

                            //total por actividad
                            $activiy_inversion = $activiy_inversion + $associated['contributions'][0]['inversion'];
                            $activiy_inversion_especie = $activiy_inversion_especie + $associated['contributions'][0]['inversionspecies'];
                            $activiy_pagado = $activiy_pagado + $associated['contributions'][0]['paid'];
                            $activiy_comprometido = $activiy_comprometido + $associated['contributions'][0]['commited'];
                            $activiy_metas = $activiy_metas + $countGoal;

                            //insertando para hacer el exel
                            $exel = new CvComandExcel();
                            $exel->id = $i;
                            $exel->programas = $program['program_name'];
                            $exel->projectos = $project['project_name'];
                            $exel->actividades = $activity['activite_name'];
                            $exel->metas = $countGoal;
                            $exel->asociados = $associated['name'];
                            $exel->inversion = $associated['contributions'][0]['inversion'];
                            $exel->especie = $associated['contributions'][0]['inversionspecies'];
                            $exel->pagado = $associated['contributions'][0]['paid'];
                            $exel->comprometido = $associated['contributions'][0]['commited'];
                            $exel->save();
                            $i = $i + 1;
//
                        }

                        //iserta totales por actividades
                        $exel = new CvComandExcel();
                        $exel->id = $i;
                        $exel->programas = $program['program_name'];
                        $exel->projectos = $project['project_name'];
                        $exel->actividades = "TOTAL POR ACTIVIDAD";
                        $exel->metas = $activiy_metas;
                        $exel->asociados = " ";
                        $exel->inversion = $activiy_inversion;
                        $exel->especie = $activiy_inversion_especie;
                        $exel->pagado = $activiy_pagado;
                        $exel->comprometido = $activiy_comprometido;
                        $exel->save();
                        $i = $i + 1;
                    }
                    //total por proyecto
                    $final_inversion = $final_inversion + $total_inversion + $total_inversion_especie;
                    $final_pagado = $final_pagado + $total_pagado;
                    $final_comprometido = $final_comprometido + $total_comprometido;
                    $final_metas = $final_metas + $total_metas;
                    //iserta totales por proyectos
                    $exel = new CvComandExcel();
                    $exel->id = $i;
                    $exel->programas = $program['program_name'];
                    $exel->projectos = "TOTAL POR PROYECTO";
                    $exel->actividades = " ";
                    $exel->metas = $total_metas;
                    $exel->asociados = " ";
                    $exel->inversion = $total_inversion;
                    $exel->especie = $total_inversion_especie;
                    $exel->pagado = $total_pagado;
                    $exel->comprometido = $total_comprometido;
                    $exel->save();
                    $i = $i + 1;
                }
            }
        }
        //inserta total final
        $exel = new CvComandExcel();
        $exel->id = $i;
        $exel->programas = "TOTAL FINAL";
        $exel->projectos = " ";
        $exel->actividades = "";
        $exel->metas = $final_metas;
        $exel->asociados = " ";
        $exel->inversion = $final_inversion;
        $exel->inversion = '';
        $exel->pagado = $final_pagado;
        $exel->comprometido = $final_comprometido;
        $exel->save();
        $i = $i + 1;
    }
    private function insertExeltableTwo($param)
    {
        $i = 1;
        $final_inversion = 0;
        $final_pagado = 0;
        $final_comprometido = 0;
        $final_metas = 0;

        foreach ($param as $program) {
            if (array_key_exists('projects', $program)) {
                foreach ($program['projects'] as $project) {
                    $total_inversion = 0;
                    $total_inversion_especie = 0;
                    $total_pagado = 0;
                    $total_comprometido = 0;
                    $total_metas = 0;
                    foreach ($project['activities'] as $activity) {
                        $activiy_inversion = 0;
                        $activiy_inversion_especie = 0;
                        $activiy_pagado = 0;
                        $activiy_comprometido = 0;
                        $activiy_metas = 0;
                        foreach ($activity['associates'] as $associated) {
                            $countGoal = 0;
                            $goal = CvGoalsForContribution::where('contributions_id', $associated['associa_contri']);
                            if ($goal->exists()) {
                                $countGoal = count($goal->get());
                            }
                            //total por proyecto
                            $total_inversion = $total_inversion + $associated['contributions'][0]['inversion'];
                            $total_inversion_especie = $total_inversion_especie + $associated['contributions'][0]['inversionspecies'];
                            $total_pagado = $total_pagado + $associated['contributions'][0]['paid'];
                            $total_comprometido = $total_comprometido + $associated['contributions'][0]['commited'];
                            $total_metas = $total_metas + $countGoal;

                            //total por actividad
                            $activiy_inversion = $activiy_inversion + $associated['contributions'][0]['inversion'];
                            $activiy_inversion_especie = $activiy_inversion_especie + $associated['contributions'][0]['inversionspecies'];
                            $activiy_pagado = $activiy_pagado + $associated['contributions'][0]['paid'];
                            $activiy_comprometido = $activiy_comprometido + $associated['contributions'][0]['commited'];
                            $activiy_metas = $activiy_metas + $countGoal;

                            //insertando para hacer el exel
                            $exel = new CvComandExcel();
                            $exel->id = $i;
                            $exel->programas = $program['program_name'];
                            $exel->projectos = $project['project_name'];
                            $exel->actividades = $activity['activite_name'];
                            $exel->metas = $countGoal;
                            $exel->asociados = $associated['name'];
                            $exel->inversion = $associated['contributions'][0]['inversion'];
                            $exel->especie = $associated['contributions'][0]['inversionspecies'];
                            $exel->pagado = $associated['contributions'][0]['paid'];
                            $exel->comprometido = $associated['contributions'][0]['commited'];
                            $exel->save();
                            $i = $i + 1;
//
                        }

                        //iserta totales por actividades
                        $exel = new CvComandExcel();
                        $exel->id = $i;
                        $exel->programas = $program['program_name'];
                        $exel->projectos = $project['project_name'];
                        $exel->actividades = "TOTAL POR ACTIVIDAD";
                        $exel->metas = $activiy_metas;
                        $exel->asociados = " ";
                        $exel->inversion = $activiy_inversion;
                        $exel->especie = $activiy_inversion_especie;
                        $exel->pagado = $activiy_pagado;
                        $exel->comprometido = $activiy_comprometido;
                        $exel->save();
                        $i = $i + 1;
                    }
                    //total por proyecto
                    $final_inversion = $final_inversion + $total_inversion + $total_inversion_especie;
                    $final_pagado = $final_pagado + $total_pagado;
                    $final_comprometido = $final_comprometido + $total_comprometido;
                    $final_metas = $final_metas + $total_metas;
                    //iserta totales por proyectos
                    $exel = new CvComandExcel();
                    $exel->id = $i;
                    $exel->programas = $program['program_name'];
                    $exel->projectos = "TOTAL POR PROYECTO";
                    $exel->actividades = " ";
                    $exel->metas = $total_metas;
                    $exel->asociados = " ";
                    $exel->inversion = $total_inversion;
                    $exel->especie = $total_inversion_especie;
                    $exel->pagado = $total_pagado;
                    $exel->comprometido = $total_comprometido;
                    $exel->save();
                    $i = $i + 1;
                }
            }
        }
        //inserta total final
        $exel = new CvComandExcel();
        $exel->id = $i;
        $exel->programas = "TOTAL FINAL";
        $exel->projectos = " ";
        $exel->actividades = "";
        $exel->metas = $final_metas;
        $exel->asociados = " ";
        $exel->inversion = $final_inversion;
        $exel->especie = '';
        $exel->pagado = $final_pagado;
        $exel->comprometido = $final_comprometido;
        $exel->save();
        $i = $i + 1;
    }

    private function excelPrint()
    {
        Excel::create('Laravel Excel', function ($excel) {

            $excel->sheet('Productos', function ($sheet) {

                $products = CvComandExcel::all();

                $sheet->fromArray($products);
            });
        })->export('xls');
    }

    //superior= Array al que se le ingresa datos
    //inferior=Array que se ingresara
    //strsup= id de validacion del array inferior
    //strinter= key que se le dara al arreglo ingresado en el superior
    //union= id de validacion del array superior
    private function insertArrayProgram($superior, $inferior, $strsup, $strinter, $union)
    {
        $position = 0;
        for ($i = 0; $i < count($superior); $i++) {
            for ($j = 0; $j < count($inferior); $j++) {
                if ($inferior[$j][$strsup] == $superior[$i][$union]) {
                    $superior[$i][$strinter][$position] = $inferior[$j];
                    $position = $position + 1;
                }
            }
            $position = 0;
        }
        return $superior;
    }

    private function insertDuplic($associated, $id)
    {
        if (count($associated) > 0) {
            for ($i = 0; $i < count($associated); $i++) {
                if ($associated[$i]["id"] == $id) {
                    return true;
                }
            }
        }
        return false;
    }

    //Control de especie
    private function controlSpecie($id_contribution, $date)
    {
        $exist = CvContributionSpecies::where('contributions_id', $id_contribution);
        if ($exist->exists()) {
            $exist->delete();
        }
        foreach ($date as $value) {
            $especie = new CvContributionSpecies();
            $especie->quantity = $value['quantity'];
            $especie->description = $value['description'];
            $especie->price_unit = $value['price_unit'];
            $especie->balance = $value['quantity'];
            $especie->contributions_id = $id_contribution;
            $especie->save();
        }
    }

    //end control especie
    //INSERTA INVERSION
    private function insertContribution(Request $date, $budget, $especie, $other, $type)
    {
        $info = array();
        $carbon = new Carbon();
        $contribution = new CvAssociatedContribution();
        $contribution->inversion = $budget;
        $contribution->inversion_origin = $budget;
        $contribution->inversion_species = $especie;
        $contribution->associated_id = $date->associated_id;
        $contribution->project_activity_id = $date->project_activity_id;
        $contribution->year = $date->year;
        $contribution->type = $type;

        if ($type == 1) {
            $contribution->balance = $budget;
        } else {
            $contribution->balance = $contribution->inversion_species;
        }

        if ($contribution->save()) {
            if ($contribution->type == 2) {

                foreach ($date->species_contribution as $value) {
                    $especie = new CvContributionSpecies();
                    $especie->quantity = $value['quantity'];
                    $especie->description = $value['description'];
                    $especie->price_unit = $value['price_unit'];
                    $especie->balance = $value['quantity'];
                    $especie->contributions_id = $contribution->id;
                    $especie->save();
                }
            }
            array_push($info, array(
                "inversion_to" => 0,
                "inversion_end" => $contribution->project_activity_id,
                "budget" => $contribution->inversion,
                "budget_species" => $contribution->inversion_species,
                "year" => $contribution->year,
                "type" => $contribution->type,
                "other" => $other,
                "Contribution_id" => $contribution->id,
            ));
            $this->history($info);
            return [
                "message" => "Inversion almacenada",
                "response_code" => 200,
            ];
        } else {
            return [
                "message" => "no se guardo la inversion",
                "response_code" => 500,
            ];
        }
    }

    //calculo de comprometido
    private function calcule($id)
    {
        $tgoal = 0;
        $obje_id = 0;
        $tbudget = 0;
        //asociado
        $asociate = CvAssociated::find($id);
        $contributionAsociated = CvAssociatedContribution::where('associated_id', $asociate->id)->get();
        foreach ($contributionAsociated as $object) {
            if ($obje_id != $object->id) {

                $tgoal = CvGoalsForContribution::where('contributions_id', $object->id)->count() + $tgoal;
                $obje_id = $object->id;
            }
            //budget
            $perShares = CvContributionPerShare::where('associated_id', $object->associated_id)->get();
            $tbudget = 0;
            foreach ($perShares as $per) {
                //Actividad
                $task = CvTask::find($per->task_id);
                $activity = CvProjectActivity::find($task->taskType->project_activity_id);
                //presupuesto por Accion
                if ($activity->id == $object->project_activity_id) {
                    $budget = CvBudget::find($per->budget_id);
                    $action = CvActions::find($budget->actionsMaterials->action_id);
                    //Calculo
                    $tbudget = $budget->value + $tbudget;
                }
            }
            //editar
            $contribuAsociated = CvAssociatedContribution::find($object->id);
            $contribuAsociated->committed_balance = $object->inversion - $tbudget;
            $contribuAsociated->save();
        }
    }

    //Historial de aportes por asociado
    private function history($date)
    {
        $history = new CvBackupContribution();
        //dd ($date[0]["inversion_to"]);
        $history->info_inversion_to = $date[0]['inversion_to'];
        $history->info_inversion_end = $date[0]['inversion_end'];
        $history->inversion = $date[0]['budget'];
        $history->inversion_species = $date[0]['budget_species'];
        $history->year = $date[0]['year'];
        $history->type = $date[0]['type'];
        $history->other = $date[0]['other'];
        $history->associated_contributions_id = $date[0]['Contribution_id'];
        $history->save();
    }

}