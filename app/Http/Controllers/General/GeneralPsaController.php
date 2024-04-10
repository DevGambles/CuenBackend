<?php

namespace App\Http\Controllers\General;

use App\CvAssociatedContribution;
use App\CvContributionSpecies;
use App\CvFilePsa;
use App\CvProcessTypePsa;
use App\CvPsaBudget;
use App\CvTaskOpen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Method\FunctionsSpecificController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class GeneralPsaController extends Controller
{
    public function insertBudget(Request $dates)
    {

        //Busca si el procedimiento se creo con predio PSA
        $process_psa= CvProcessTypePsa::where('proccess_id',$dates->proccess_id)->exists();
        if (!$process_psa){
            return[
                "message"=>"El proceso no tiene predio PSA",
                "code"=>500
            ];
        }

        $budget_month=new CvPsaBudget();
        $budget_month->month= $dates->budgetOpen['month'];
        $budget_month->value_month= $dates->valuePerMonth;
        $budget_month->value_total= $dates->numberMonth* (float)$dates->valuePerMonth;
        $budget_month->proccess_id= $dates->proccess_id;
        foreach ($dates->budgetOpen as $budgetOpen){
            $result=$this->commitedCal($budgetOpen['contribution_id'], $budget_month->value_total);
            //Si hay error en el calculo de comprometido
            if ($result != 1){
                return $result;
            }
        }


        $budget_month->save();
        return [
            "message"=>"Se ha almacenado un presupuesto PSA",
            "code" =>200
        ];
    }

    //Calcula el comprometido de la contribucion
    public function commitedCal($contributio_id, $value_total){
        //Busca la contribucion seleccionada
        $contribution=CvAssociatedContribution::find($contributio_id);
        if ($contribution->balance <  $value_total){
            return[
                "message"=>"El saldo disponible no alcanza para el presupuesto dado",
                "code"=>500
            ];
        }

        if ($contribution->committed == null || $contribution->committed == 0){
            //si la contribucion no tiene un comprometido solo se le asigna
            $contribution->committed = $value_total;
        }else{
            //si la contribucion ya tiene un saldo comprometido este se suma
            $contribution->committed =  $contribution->committed + $value_total;
        }
        //el saldo del comprometido es igual a lo que se comprometio dar menos el saldo actual
        $contribution->committed_balance = (double) $contribution->balance - (double) $contribution->committed ;

        if ( $contribution->committed_balance < 0){
            return[
                "message"=>"El presupuesto dado es mas alto de lo disponible",
                "code"=>500
            ];
        }
        $contribution->save();
        return 1;
    }

    public function calcSpeciesCommand($dates)
    {
        $total_value = 0;
        $contribution = CvAssociatedContribution::find($dates['contributions_id']);

        if($contribution->type == 2){
            $value_specie = 0;
            $all_specie = CvContributionSpecies::find($dates['id']);
            if ($all_specie->contributions_id != $dates['contributions_id']){
                return[
                    'message'=>"La especie no corresponde a la contibucion dada",
                    'code'=>500
                ];
            }
            if ($all_specie->balance > 0 && $dates['quantity'] <= $all_specie->balance ){
                $all_specie->balance= (double) $all_specie->balance - (double)$dates['quantity'];
                $all_specie->used= (double) $all_specie->used + (double) $dates['quantity'];

                if ($all_specie->balance <= 0 || $all_specie->used >=  $all_specie->quantity){

                    $all_specie->balance=0;
                    $all_specie->used=$all_specie->quantity;
                }
                $all_specie->save();
                $value_specie= $dates['quantity'] * $all_specie->price_unit;
                $total_value= $total_value + $value_specie;
                return $this->commitedCal($contribution->id,$total_value);
            }else{
                return[
                    'message'=>"La contibucion no cuenta con fondos suficientes",
                    'code'=>500
                ];
            }
        }else{
            return[
                'message'=>"La contibucion no es de typo especie",
                'code'=>500
            ];
        }

    }

    public function insertDocumenttask(Request $dates, $type)
    {
        /*
         * type file:
         * 1 = PSA
         * 2 = Fuentes hidricas
         * 3 = procesos erosivos
         * 4 = excel de financiero
         * 5 = procesos de stards
         * */

        foreach (CvFilePsa::where('state_delete',0)->where('type', $type)->get() as $detal_file){
            $delet=CvFilePsa::find($detal_file->id);
            $delet->state_delete = 1;
            $delet->save();
        }

        $file=$dates->file;
        $ffile = $dates->file;
        $ffile = str_replace('/tmp/', '', $ffile);
        $filename = sha1(time().$ffile);
        $extension=$file->getClientOriginalExtension();
        $nameFile= $filename.'_'. $file->getClientOriginalName().'.'.$extension;

        Storage::disk('local')->put('documents/' . $nameFile, File::get($file));

        $file_open=new CvFilePsa();
        $file_open->name = $nameFile;
        $file_open->state_delete = 0;
        $file_open->user_id = $this->userLoggedInId();
        $file_open->type = $type;
        $file_open->save();

        return[
            "message"=>"Archivo almacenado",
            "code"=>200
        ];


    }

    public function getFile($type)
    {
        /*
         * type file:
         * 1 = PSA
         * 2 = Fuentes hidricas
         * 3 = procesos erosivos
         * 5 = procesos stards
         * */
        return CvFilePsa::where('state_delete',0)->where('type', $type)->get()->last();
    }

}
