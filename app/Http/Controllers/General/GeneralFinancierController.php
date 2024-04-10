<?php

namespace App\Http\Controllers\General;

use App\CvAssociated;
use App\CvAssociatedContribution;
use App\CvBackupFinancierSpecieCommand;
use App\CvContributionSpecies;
use App\CvFilePsa;
use App\CvFinacierCommandDetail;
use App\CvFinancierCommandDetails;
use App\CvFinancierDetailCode;
use App\CvFinancierLoadExcel;
use App\CvFinancierSpecieCommand;
use App\CvFinancingExpense;
use App\CvIncome;
use App\CvProgram;
use App\CvProjectActivity;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class GeneralFinancierController extends Controller
{

    public function getInfoLoadExel()
    {
        $info=array();
        $excel=CvFilePsa::where('type',4)->where('state_delete',0)->first();
        $user=$excel->user;
        $info['user']=$user->name;
        $info['create_at']=$excel->created_at->format('Y-m-d H:i:s');

        return $info;

    }

    public function getLastLoadExel()
    {
        $excel=CvFilePsa::where('type',4)->where('state_delete',0)->first();
        return Storage::disk('public')->get('financierModule/'.$excel->name);
    }

    public function loadExcel(Request $request)
    {
        // Excel::load($request->file('file'), function ($reader) {
        //     $validate = 1;
        //     /* Esto valida que el documento no tenga basura
        //     foreach ($reader->get() as $userPoint) {
        //            if ($userPoint['cod_cco'] == null || $userPoint['cod_cco'] == 0 || $userPoint['cod_cco'] == "0") {
        //                $validate = 0;
        //            }
        //        }
        //        if ($validate == 0) {
        //            $financier_load = new CvFinancierLoadExcel();
        //            $financier_load->detail_json = ('{"info_general": {},"form": {},"type":0}');
        //            $financier_load->value = 0;
        //            $financier_load->type = 0;
        //            $financier_load->user_id = $this->userLoggedInId();
        //            $financier_load->save();
        //        }*/
        //     if ($validate == 1) {
        //         foreach ($reader->get() as $userPoint) {

        //             $financier_load = new CvFinancierLoadExcel();
        //             $financier_load->detail_json = json_encode($userPoint, true);
        //             $financier_load->value = $userPoint['deb_mov'];
        //             $financier_load->user_id = $this->userLoggedInId();

        //             //buscar codigo
        //             $code = CvFinacierCommandDetail::where('code', $userPoint['cod_cco']);

        //             if ($code->exists($code)) {

        //                 $contribution_detail = CvFinacierCommandDetail::find($code->first()->id);
        //                 $contribution_associate = CvAssociatedContribution::find($contribution_detail->contributionAssociated->id);
        //                 $balance_last=$contribution_detail->balance;
        //                 $committed_last=$contribution_associate->committed;
        //                 $committed_balance_last=$contribution_associate->committed_balance;

        //                 $paid_int = intval($userPoint['deb_mov']);

        //                 //Si lo disponible es mayor o igual a lo pagado
        //                 if ($contribution_detail->balance >= $paid_int && $contribution_associate->balance >= $paid_int) {

        //                     $contribution_detail->paid = $contribution_detail->paid + $paid_int;
        //                     $contribution_detail->balance = $contribution_detail->balance - $paid_int;

        //                     //contribucion por asociado
        //                     $contribution_associate->balance = $contribution_associate->balance - $paid_int;
        //                     $contribution_associate->paid = $contribution_associate->paid + $paid_int;
        //                     if ($paid_int >= $contribution_associate->committed) {
        //                         $contribution_associate->committed = 0;
        //                         $contribution_associate->committed_balance = $contribution_associate->balance;
        //                     } else {
        //                         $contribution_associate->committed = $contribution_associate->committed - $paid_int;
        //                         $contribution_associate->committed_balance = $contribution_associate->balance - $contribution_associate->committed;
        //                     }
        //                     $contribution_detail->save();
        //                     $contribution_associate->save();
        //                     $contribution_detail['balance_last']=$balance_last;
        //                     $contribution_detail['committed_last']=$committed_last;
        //                     $contribution_detail['committed_balance_last']=$committed_balance_last;
        //                     $financier_load->type = 1;
        //                     $contribution_detail['type_table_save']=2;
        //                     $contribution_detail['price_used']=0;
        //                     $contribution_detail['cv_contribution_species']=0;
        //                     $contribution_detail['paid_last']=0;
        //                     $contribution_detail['add_used']=0;
        //                     $contribution_detail['price_used']=0;
        //                     $this->historyCommandLevelGeneral($contribution_detail);
        //                 } else {
        //                     $financier_load->type = 2;
        //                 }
        //             } else {
        //                 $code_expenses=CvFinancingExpense::where('codeCenter', $userPoint['cod_cco']);
        //                 if ($code_expenses->exists()){
        //                     $paidexpense=$code_expenses->first();
        //                     if($paidexpense->balance >= $financier_load->value){
        //                         $paidexpense->balance=$paidexpense->balance-$financier_load->value;
        //                         $paidexpense->payed=$financier_load->value;
        //                         $paidexpense->save();
        //                         $financier_load->type = 1;

        //                     }else{
        //                         $financier_load->type = 2;
        //                     }
        //                 }elseif ($userPoint['cod_cco'] == '001' || $userPoint['cod_cco'] == 001 || $userPoint['cod_cco'] == "001") {
        //                     $financier_load->type = 4;
        //                 } else {
        //                     $financier_load->type = 3;
        //                 }
        //             }

        //             $financier_load->save();
        //         }
        //     }
        // });
        $financier = CvFinancierLoadExcel::all()->last();
        if ($financier->type == 0) {
            return [
                "message" => "El excel cargado tiene informacion no valida",
                "code" => 500
            ];
        } else {
            foreach (CvFilePsa::where('state_delete',0)->where('type',4)->get() as $detal_file){
                $delet=CvFilePsa::find($detal_file->id);
                $delet->state_delete = 1;
                $delet->save();
            }

            $file=$request->file;
            $ffile = $request->file;
            $ffile = str_replace('/tmp/', '', $ffile);
            $filename = sha1(time().$ffile);
            $nameFile= $filename.'_'. $file->getClientOriginalName();

            Storage::disk('public')->put('financierModule/' . $nameFile, File::get($file));

            $file_open=new CvFilePsa();
            $file_open->name = $nameFile;
            $file_open->state_delete = 0;
            $file_open->user_id = $this->userLoggedInId();
            $file_open->type = 4;
            $file_open->save();

            return [
                "message" => "El excel fue cargado completamente",
                "code" => 200
            ];
        }
        return $financier;
    }

    public function getContribution()
    {
        $info = array();

        $project = CvProjectActivity::all();
        foreach ($project as $act) {

            $act->associatedContribution;
            $associates = array();
            foreach ($act->associatedContribution as $contribution) {

                if ($contribution->type == 1) {
                    array_push($associates, array(
                        "associate_id" => $contribution->thisisassociate->id,
                        "associate_name" => $contribution->thisisassociate->name,
                        "contribution_id" => $contribution->id,
                        "contribution_paid" => $contribution->paid,
                        "contribution_balance" => $contribution->balance,
                        "contribution_committed" => $contribution->committed,
                        "contribution_inversion" => $contribution->inversion,
                        "contribution_inversion_species" => $contribution->inversion_species,
                        "contribution_type" => $contribution->type,
                    ));
                }
            }
            if ($associates != []) {
                $all_action = array();
                foreach ($act->financerAction as $action) {

                    array_push($all_action, array(
                        "action_id" => $action->id,
                        "action_name" => $action->name,
                        "details" => $action->financierDetail,
                    ));

                }

                array_push($info, array(
                    "project_id" => $act->projectByActivity[0]->id,
                    "activite_id" => $act->id,
                    "activite_name" => $act->name,
                    "associates" => $associates,
                    "actions" => $all_action,

                ));
            }

        }

        return $info;

    }

    public function programProject()
    {
        $programs=CvProgram::all();
        foreach ($programs as $program){
            $program->programByProject;
        }
        return $programs;
    }

    public function insertContribution(Request $date, $a = null)
    {
        $associate = CvAssociated::find($date->associated_id);
        $detail = CvFinancierDetailCode::find($date->detail_id);
        $code = $associate->code . $detail->code;

        $validate = CvFinacierCommandDetail::where('associated_id', $date->associated_id)
            ->where('financier_detail_id', $date->detail_id)
            ->where('code', $code);
        if ($validate->exists()) {
            return [
                "message" => "Ya existe una contribucion para el detalle y asociado ingresado",
                "code" => 500
            ];
        }


        $carbon = new Carbon();
        $contribution = new CvFinacierCommandDetail();
        $contribution->associated_id = $date->associated_id;
        $contribution->financier_detail_id = $date->detail_id;
        $contribution->contributions_id = $date->contribution_id;
        $contribution->inversion = $date->value;
        $contribution->balance = $date->value;
        $contribution->type = 1;
        $contribution->code = $code;
        $contribution->paid = 0;
        $contribution->year = $date->year;
        $contribution->committed = 0;
        $contribution->committed_balance =  $contribution->balance;
        $contribution->save();
        $contribution['type_table_save']=1;
        $contribution['price_used']=0;
        $contribution['cv_contribution_species']=0;
        $contribution['paid_last']=0;
        $contribution['balance_last']=0;
        $contribution['committed_last']=0;
        $contribution['committed_balance_last']=0;
        $contribution['add_used']=0;
        $contribution['price_used']=0;

        $this->historyCommandLevelGeneral($contribution);

        if($a==1){
            return $contribution->id;
        }
        else {
            return $contribution;
        }
    }

    public function getLoadExcelClasificate(Request $dates)
    {

        $success = array();
        $balancefail = array();
        $nocode = array();
        $special = array();
        $trash = array();
        $alldata = array();
        $from = date(date($dates->from, strtotime("-1 month")) . ' 00:00:00', time()); //need a space after dates.
        $to = date($dates->to . ' 23:59:59', time());

        $current = CvFinancierLoadExcel::whereBetween('created_at', array($from, $to))->where('type', '<>', 0)->get();

        foreach ($current as $data) {
            $load = json_decode($data->detail_json, true);
            if ($load['cod_cco'] == null) {
                $code = 'no valido';
            } else {
                $code = $load['cod_cco'];
            }

            $user = User::find($data->user_id);
            $info = array();
            $info["user_name"] = $user->name;
            $info["value"] = $data->value;
            $info["created_at"] = $data->created_at->format('Y-m-d H:i:s');
            $info["code"] = $code;
            $info["name_ter"] = $load['nom_ter'];
            $info["description_ter"] = $load['des_mov'];


            if ($data->type == 1) {
                array_push($success, $info);
            }
            if ($data->type == 2) {
                array_push($balancefail, $info);
            }
            if ($data->type == 3) {
                array_push($nocode, $info);
            }
            if ($data->type == 4) {
                array_push($special, $info);
            }
            if ($data->type == 5) {
                array_push($trash, $info);
            }
        }

        $alldata['type_1'] = $success;
        $alldata['type_2'] = $balancefail;
        $alldata['type_3'] = $nocode;
        $alldata['type_4'] = $special;
        $alldata['type_5'] = $trash;

        return $alldata;

    }

    public function getCommandDetail()
    {
        $info = array();
        $all_contribution = CvAssociatedContribution::where('type',1)->get();
        //contribuciones
        foreach ($all_contribution as $contribution) {

            $activite = $contribution->projectActivity;
            $associate = $contribution->associated;
            $actions = $activite->financerAction;
            //acciones
            foreach ($actions as $actio) {
                $detail_all = $actio->financierDetail;
                //detalles
                foreach ($detail_all as $detail) {
                    $all_command = $detail->detailCommandFinancier;
                    foreach ($all_command as $command) {
                        $command->contributionAssociated;
                    }
                }
            }
            if (CvFinacierCommandDetail::where('contributions_id',$contribution->id)->where('associated_id',$contribution->associated_id)->exists()){
                array_push($info, $contribution);
            }
        }

        return $info;
    }

    public function getCommandDetailspecific($id)
    {
        $detail = CvFinacierCommandDetail::find($id);
        $detail->associate;
        return $detail;
    }

    public function updateContributionDetail(Request $data)
    {
        $contribution = CvFinacierCommandDetail::find($data->contibution_detail_id)->where('contributions_id', $data->contribution_id)->first();

        if (empty($contribution)) {
            return [
                "message" => "No existe la contibucion a nivel de detalle",
                "code" => 500
            ];
        }
        $total=0;
        $all_detail_contri = CvFinacierCommandDetail::where('contributions_id', $data->contribution_id)->get();
        foreach ( $all_detail_contri as $detail_contri){
            $total = $total + $detail_contri->inversion;
        }
        if ($contribution->contributionAssociated->inversion >= $total){

            $total = $total -  $contribution->inversion ;
            $contribution->inversion = $data->value;

            $contribution->balance = $data->value - $contribution->paid;

            if ($contribution->balance < 0) {
                return [
                    "message" => "La contribucion tiene un saldo pagado y su saldo disponible no debe ser menor a 0",
                    "code" => 500
                ];
            }

            $total= $total +  $contribution->inversion;

            if ($contribution->contributionAssociated->inversion >= $total){

                $contribution->save();
                return [
                    "message" => "La contribucion a nivel de detalle fue actualizada",
                    "code" => 200
                ];
            }
        }
        return [
            "message" => "La contribucion total es menor a lo asignado",
            "code" => 200
        ];


    }

    public function transactionDetail(Request $data)
    {
        $newTransaction = false;
        $contri_to=CvFinacierCommandDetail::find($data->to_contibution_detail_id);
        $contri_to_associate=CvAssociatedContribution::find($contri_to->contributions_id);

        $contri_form=CvFinacierCommandDetail::where('financier_detail_id',$data->form_contibution_detail_id)
            ->where('associated_id',$data->associated_id)
            ->where('contributions_id',$data->contribution_id)
            ->first();
        if ($contri_form == null) {
            $idContriForm = $this->insertContribution($data, 1);
            $contri_form = CvFinancierCommandDetails::findOrFail($idContriForm);
            $newTransaction = true;
        }
        $contri_form_associate=CvAssociatedContribution::find($contri_form->contributions_id);
        //--DATA HISTORY--//
        $tobalance_last=$contri_to->balance;
        $tocommitted_last=$contri_to->committed;
        $tocommitted_balance_last=$contri_to->committed_balance;

        $formbalance_last=$contri_form->balance;
        $formcommitted_last=$contri_form->committed;
        $formcommitted_balance_last=$contri_form->committed_balance;
        //--END DATA HISTORY--//
        $contri_to->inversion = $contri_to->inversion -  $data->value;
        $contri_to->balance = $contri_to->inversion  - $contri_to->paid;

        $contri_to_associate->inversion = $contri_to_associate->inversion -  $data->value;
        $contri_to_associate->balance = $contri_to_associate->inversion - $contri_to_associate->paid;
        $contri_to_associate->committed_balance = $contri_to_associate->balance - $contri_to_associate->committed;

        if (!$newTransaction){
            $contri_form->inversion = $contri_form->inversion +  $data->value;
            $contri_form->balance = $contri_form->inversion  - $contri_form->paid;
        }

        $contri_form_associate->inversion = $contri_form_associate->inversion +  $data->value;
        $contri_form_associate->balance = $contri_form_associate->inversion - $contri_form_associate->paid;
        $contri_form_associate->committed_balance = $contri_form_associate->balance - $contri_form_associate->committed;

        if ($contri_to->inversion < 0 ){
            return [
                "message" => "La inversion es menor a cero",
                "code" => 500
            ];
        }
        if ($contri_to->balance < 0 ||  $contri_to_associate->balance < 0) {
            return [
                "message" => "La contribucion tiene un saldo pagado y su saldo disponible no debe ser menor a 0",
                "code" => 500
            ];
        }
        if( $contri_to_associate->committed_balance < 0){
            return [
                "message" => "La contribucion tiene un saldo comprometido y su saldo disponible no debe ser menor a 0",
                "code" => 500
            ];
        }

        $contri_to->save();
        $contri_to_associate->save();
        $contri_form->save();
        $contri_form_associate->save();

        $contri_to['balance_last']=$tobalance_last;
        $contri_to['committed_last']=$tocommitted_last;
        $contri_to['committed_balance_last']=$tocommitted_balance_last;

        $contri_form['balance_last']=$formbalance_last;
        $contri_form['committed_last']=$formcommitted_last;
        $contri_form['committed_balance_last']=$formcommitted_balance_last;

        $contri_to['type_table_save']=1;
        $contri_to['price_used']=0;
        $contri_to['cv_contribution_species']=0;
        $contri_to['paid_last']=0;
        $contri_to['add_used']=0;
        $contri_to['price_used']=0;

        $contri_form['type_table_save']=1;
        $contri_form['price_used']=0;
        $contri_form['cv_contribution_species']=0;
        $contri_form['paid_last']=0;
        $contri_form['add_used']=0;
        $contri_form['price_used']=0;

        $this->historyCommandLevelGeneral($contri_to);
        $this->historyCommandLevelGeneral($contri_form);

        return [
            "message" => "La contribucion se ha transferido correctamente",
            "code" => 200
        ];
    }

    public function getContributionSpecie()
    {
        $info = array();

        $project = CvProjectActivity::all();
        foreach ($project as $act) {

            $act->associatedContribution;
            $associates = array();
            foreach ($act->associatedContribution as $contribution) {

                if ($contribution->type == 2) {

                    array_push($associates, array(
                        "associate_id" => $contribution->thisisassociate->id,
                        "associate_name" => $contribution->thisisassociate->name,
                        "contribution_id" => $contribution->id,
                        "contribution_paid" => $contribution->paid,
                        "contribution_balance" => $contribution->balance,
                        "contribution_committed" => $contribution->committed,
                        "contribution_inversion" => $contribution->inversion,
                        "contribution_inversion_species" => $contribution->inversion_species,
                        "contribution_type" => $contribution->type,
                        "contribution_specie" => $contribution->species,
                    ));
                }
            }
            if ($associates != []) {
                $all_action = array();
                foreach ($act->financerAction as $action) {

                    array_push($all_action, array(
                        "action_id" => $action->id,
                        "action_name" => $action->name,
                        "details" => $action->financierDetail,
                    ));

                }

                array_push($info, array(
                    "project_id" => $act->projectByActivity[0]->id,
                    "activite_id" => $act->id,
                    "activite_name" => $act->name,
                    "associates" => $associates,
                    "actions" => $all_action,

                ));
            }

        }

        return $info;
    }

    public function insertContributionSpecie(Request $data)
    {
        $contri_specie= CvContributionSpecies::find($data->contribution_specie_id);
        $price_used=$data->used * $contri_specie->price_unit;

        $exist_contri=CvFinancierSpecieCommand::where('contributions_specie_id',$data->contribution_specie_id)
            ->where('financier_detail_id',$data->detail_id);

        $contribution = CvAssociatedContribution::find($contri_specie->contributions->id);

        if($exist_contri->exists()){
            $add_specie=CvFinancierSpecieCommand::find($exist_contri->first()->id);
            $add_specie->price_used=$add_specie->price_used + $price_used;
            $add_specie->add_used=$add_specie->add_used+$data->used;
        }else{
            $add_specie=new CvFinancierSpecieCommand();
            $add_specie->add_used=$data->used;
            $add_specie->price_used=$price_used;
            $add_specie->contributions_specie_id=$data->contribution_specie_id;
            $add_specie->financier_detail_id=$data->detail_id;
        }

        $contri_specie->balance= $contri_specie->balance-$data->used;
        $contri_specie->used= $contri_specie->used+$data->used;

        if ($contri_specie->balance < 0 ||   $contri_specie->used > $contri_specie->quantity){
            return [
                "message" => "no tiene suficientes unidades disponibles",
                "code" => 500
            ];
        }

        //Cuadro de mando y control
        if ($contribution->balance >= $price_used && $contribution->type == 2 ) {
            //contribucion por asociado
            $contribution->balance = $contribution->balance - $price_used;
            $contribution->paid = $contribution->paid + $price_used;
            if ($price_used >= $contribution->committed) {
                $contribution->committed = 0;
                $contribution->committed_balance = $contribution->balance;
            } else {
                $contribution->committed = $contribution->committed - $price_used;
                $contribution->committed_balance = $contribution->balance - $contribution->committed;
            }
        }else{
            return [
                "message" => "no se puede editar la contribucion de comando devido a su disponibilidad en especie",
                "code" => 500
            ];
        }
        $add_specie->save();
        $contribution->save();

        return [
            "message" => "se ha agregado la especie",
            "code" => 200
        ];
    }

    public function historyCommandLevelGeneral($data)
    {
        $history = new CvBackupFinancierSpecieCommand();
        $history->type=$data['type'];
        $history->last=$data['inversion'];
        $history->id_save=$data['id'];
        $history->type_table_save=$data['type_table_save'];
        $history->cuantity=$data['quantity'];
        $history->dedication=$data['dedication'];
        $history->unit_measurement=$data['unit_measurement'];
        $history->cuantity_measurement=$data['quantity_measurement'];
        $history->benefit_factor=$data['benefic_factor'];
        $history->value_unit=$data['value_unit'];
        $history->code=$data['code'];
        $history->add_used=$data['add_used'];
        $history->price_used=$data['price_used'];
        $history->cv_contribution_species=$data['cv_contribution_species'];
        $history->contributions_id=$data['contributions_id'];
        $history->price_used=$data['price_used'];
        $history->cv_contribution_species=$data['cv_contribution_species'];
        $history->financier_detail_id=$data['financier_detail_id'];
        $history->associated_id=$data['associated_id'];
        $history->user_id=$this->userLoggedInId();
        $history->paid_last=$data['paid_last'];
        $history->paid_new=$data['paid'];
        $history->balance_last=$data['balance_last'];
        $history->balance_new=$data['balance'];
        $history->committed_last=$data['committed_last'];
        $history->committed_new=$data['committed'];
        $history->committed_balance_last=$data['committed_balance_last'];
        $history->committed_balance_new=$data['committed_balance'];
        if ($history->committed_new ==null){
            $history->committed_new=0;
        }
        if ($history->committed_balance_new ==null){
            $history->committed_new=0;
        }

        $history->save();

    }

    public function createIncome(Request $request){
        $campos = $request->all();

        return CvIncome::create($campos);
    }

    public function getAllIncomes() {
        $dataModel = CvIncome::all();
        return $dataModel;
    }

    public function updateIncome(Request $request){
        $campos = $request->all();
        $model = CvIncome::findOrFail($campos['id']);

        if ($model->update($campos)) {
            return $model;
        } else {
            return [
                'message' => 'error',
                'code' => '500'
            ];
        }

    }

}
