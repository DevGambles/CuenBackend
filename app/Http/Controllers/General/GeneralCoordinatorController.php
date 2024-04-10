<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvAssociatedContribution;
use App\CvActivityCoordination;
use Illuminate\Support\Facades\Auth;
use App\CvRole;
use App\User;
use App\CvProcess;

class GeneralCoordinatorController extends Controller {

    public function CoordinatingBudget() {
        $arrBudget = Array();

        array_push($arrBudget, $this->getDatBudgetByCoordinationId(9));
        array_push($arrBudget, $this->getDatBudgetByCoordinationId(10));
        array_push($arrBudget, $this->getDatBudgetByCoordinationId(13));

        return $arrBudget;
    }

    public function getBudgetByCoordination() {
        $arrResponse = Array();


        $user = Auth::user();
        switch ($user->role->id) {
            case 9:
            case 10:
            case 13:
            $arrResponse = $this->getDatBudgetByCoordinationId($user->role->id);
        }

        return $arrResponse;
    }

    private function getDatBudgetByCoordinationId($idRol){

        $acti_coord = CvActivityCoordination::where('role_id', $idRol);
        $info = array();
        $total = array();
        $inversion = 0;
        $species = 0;
        $pay = 0;
        $totales = 0;
        $toinversion = 0;
        $tospecies = 0;
        $topay = 0;
        $tototales = 0;
        $rol = 0;
        $userRol = Auth::User()->role->name;

        if ($acti_coord->exists()) {
            $acti_coord = $acti_coord->get();

            foreach ($acti_coord as $act) {

                $toinversion = 0;
                $tospecies = 0;
                $topay = 0;
                $tototales = 0;
                $tototalCommitted = 0;

                $committed = 0;

                foreach ($act->Activity as $value) {

                    $inversion += $value->inversion;
                    $committed += $value->committed;
                    $species += $value->inversion_species;
                    $pay += $value->paid;
                }

                $rol = $act->role_id;
                $totales = $inversion + $species;
                array_push($info, array(
                    "rol" => $rol,
                    "inversion" => $inversion,
                    "inversion_species" => $species,
                    "inversion_paid" => $pay,
                    "inversion_total" => $totales,
                    "committed" => $committed,
                ));
                //SETIAR VALORES
                $inversion = 0;
                $species = 0;
                $pay = 0;
                $totales = 0;
                $rol = 0;
                // END SETIAR VALORES
            }

            $infoOrder = array_sort($info, 'rol');
            for ($i = 0; $i < count($infoOrder); $i++) {

                $toinversion += $infoOrder[$i]['inversion'];
                $tospecies += $infoOrder[$i]['inversion_species'];
                $topay += $infoOrder[$i]['inversion_paid'];
                $tototales += $infoOrder[$i]['inversion_total'];
                $tototalCommitted += $infoOrder[$i]['committed'];
            }

            $userRol = CvRole::find($idRol)->name;

            $available = ($tototales - $topay);
            array_push($total, array(
                "rol" => $userRol,
                "inversion" => $toinversion,
                "inversion_species" => $tospecies,
                "inversion_paid" => $topay,
                "inversion_total" => $tototales,
                "inversion_disponible" => $available,
                "committed" => $tototalCommitted,
                "percentAvailable" => (($available - $tototalCommitted - $topay ) * 100)/$tototales,
                "percentCommitted" => ($tototalCommitted * 100) / $tototales,
            ));
            return $total[0];
//     ESTE CODIGO HAYA LA INVERSION, INVERSION EN ESPECIES, EL TOTAL DE LAS INVERSIONES Y EL TOTAL DISPONIBLE PARA CUALQUIER ROL QUE TENGA ACTIVIDADES
//            for ($i = 0; $i < count($info); $i++) {
//                $rol = $info[$i]['rol'];
//                $toinversion = $info[$i]['inversion'];
//                $tospecies = $info[$i]['inversion_species'];
//                $topay = $info[$i]['inversion_paid'];
//                $tototales = $info[$i]['inversion_total'];
//
//                for ($j = 1; $j < count($info); $j++) {
//                    if ($rol == $info[$j]['rol']) {
//                        $toinversion = $toinversion + $info[$j]['inversion'];
//                        $tospecies = $tospecies + $info[$j]['inversion_species'];
//                        $topay = $topay + $info[$j]['inversion_paid'];
//                        $tototales = $tototales + $info[$j]['inversion_total'];
//                    }
//                    $i = $j;
//                }
//
//                array_push($total, array(
//                    "rol" => CvRole::find($rol)->name,
//                    "inversion" => $toinversion,
//                    "inversion_species" => $tospecies,
//                    "inversion_paid" => $topay,
//                    "inversion_total" => $tototales,
//                    "inversion_disponible" => $tototales - $topay,
//                ));
//            }
        } else {
            if ($idRol === 9)
                $idRol = 15;
            
            return array(
                "rol" => CvRole::find($idRol)->name,
                "inversion" => $toinversion,
                "inversion_species" => $tospecies,
                "inversion_paid" => $topay,
                "inversion_total" => $tototales,
                "inversion_disponible" => $tototales - $topay,
            );
        }
    }

}
