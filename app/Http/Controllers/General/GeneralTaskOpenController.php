<?php

namespace App\Http\Controllers\General;

use App\CvAssociatedContribution;
use App\CvBackupTaskOpenAndEspecial;
use App\CvComment;
use App\CvCommentByOtherTask;
use App\CvCommunicationFormsJson;
use App\CvFileForFormsTaskOpen;
use App\CvFileOpen;
use App\CvFilesFormComunication;
use App\CvFormatDetallCotractor;
use App\CvOtherCampsTaskOpens;
use App\CvTaskExecution;
use App\CvTaskOpen;
use App\CvTaskOpenByTaskExecution;
use App\CvTaskOpenGeoMap;
use App\CvTaskStatus;
use App\CvPotentialProperty;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Method\FunctionsSpecificController;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GeneralTaskOpenController extends Controller {

    //*** Consultar tareas abiertas ***//

    public function consultTaskOpen() {

        $listsTasks = CvTaskOpen::where('state', 0)->orderBy('id', 'desc')->get();

        switch ($this->userLoggedInRol()) {
            case 12:
                $listsTasks;
                break;
            case 9:
                $listsTasks = $this->getTaskOpenFilterByIdRols($listsTasks, [9,15]);
                break;
            case 10:
                $listsTasks = $this->getTaskOpenFilterByIdRols($listsTasks, [10,16]);
                break;
            case 13:
                $listsTasks = $this->getTaskOpenFilterByIdRols($listsTasks, [13, 17]);
                break;

            default:
                $listsTasks = User::find($this->userLoggedInId())->taskOpen;
                break;
        }
        $info = array();

        if ($listsTasks) {

            foreach ($listsTasks as $listTask) {

                //--- Tareas abiertas de los usuarios ---//
                $infoTask = CvTaskOpen::find($listTask->id);
                $dateNowSubtract = $infoTask->where("id", $listTask->id)->whereBetween('date_end', [Carbon::now()->subDays(5), Carbon::now()])->exists();

                //--- Validar que la fecha de finalizacion de la tarea sea menor a la actual menos 5 dias ---//
                if ($infoTask->date_end < Carbon::now()->subDays(5)) {
                    $infoTask->task_status_id = 3;
                }

                //--- Validar que la fecha de finalizacion se encuentre en un rango de 5 dias a la actual ---//
                if ($dateNowSubtract == true) {
                    $infoTask->task_status_id = 2;
                }

                //--- Guardar los cambios del estado de la tarea ---//
                $infoTask->save();

                $listTask["task_status_name"] = CvTaskStatus::find($listTask['task_status_id'])->name;
                $subtype = $listTask->subtypes;
                if ($listTask->process->type_process == 'erosion' & $listTask->users->role->id == 3) {
                    $subtype['name'] = 'Asignar a guardacuenca';
                }
                $listTask["sub_type"] = $subtype;
                $listTask->process;

                //--- Validar si es erosivo o starts ---//
                switch ($listTask->process->type_process) {
                    case 'erosion':
                        $listTask["erosivoOrStards"] = 0;
                        break;
                    case 'stards':
                        $listTask["erosivoOrStards"] = 1;
                        break;
                    
                    default:
                        $listTask["erosivoOrStards"] = null;
                        break;
                }

                $listTask["process_id"] = $listTask->process->id;

                //*** Consultar predio de la tarea ***//
                $propertyPotentialConsult = CvPotentialProperty::where("id", $listTask->process->potential_property_id)->first();
                
                $listTask["property_name"] = ''; //georgi
                if ($propertyPotentialConsult != null)
                    $listTask["property_name"] = $propertyPotentialConsult["property_name"];

                $listTask["open"] = True;

                array_push($info, $listTask);
            }
        }

        return $info;
    }

    public function getTaskOpenFilterByIdRols($allTaskOpen, $roles) {
        $dataReponse = new Collection();

        foreach ($allTaskOpen as $item) {
            if ($item->taskOpenBudget){
                if ($item->taskOpenBudget->associateContribution->projectActivity->bycoordination)
                    $rolActivity = $item->taskOpenBudget->associateContribution->projectActivity->bycoordination->roleadd;
                foreach ($roles as $role) {
                    if ($rolActivity->id == $role){
                        $dataReponse->push($item);
                    }
                }
            }
        }
        return $dataReponse;
    }

    //*** Consultar tarea abierta en especifico ***//

    public function consultTaskOpenSpecific($id) {

        $taskOpen = CvTaskOpen::find($id);

        if (empty($taskOpen)) {
            return[
                "message" => "La tarea abierta no existe en el sistema",
                "code" => 500
            ];
        }

        if ($taskOpen->process->type_process == 'erosion' & $taskOpen->users->role->id == 3) {
            $taskOpen->subtypes['name'] = 'Asignar a guardacuenca';
        }

        foreach ($taskOpen->taskOpenBudgetMany as $item) {
            $item->associateContribution->associated;
            $item->associateContribution->projectActivity;
        }
        $taskOpen->subtypes;
        $taskOpen->process;
        $taskOpen->users;
        $taskOpen['location'] = $this->bringCoordinates($id);

        return $taskOpen;
    }

    protected function insertDocumenttaskOpen(Request $data) {

        if (CvTaskOpen::where('id', $data->task_id)->exists()) {
            $arrayFiles = $data->file('files');

            $comments = json_decode($data->comments, true);

            if (is_array($arrayFiles) && !empty($arrayFiles)) {

                foreach ($arrayFiles as $file) {
                    $id_file_save = $this->saveFileOpenTask($file, $data->task_id, $data->type);
                    if (!empty($comments)) {
                        foreach ($comments as $key => $value) {
                            if ($file->getClientOriginalName() == $key) {
                                $data_file = CvFileOpen::find($id_file_save);
                                $data_file->description = $value;
                                $data_file->save();
                            }
                        }
                    }
                }
            }
            return[
                "message" => "Archivo almacenado",
                "code" => 200
            ];
        } else {
            return[
                "message" => "La tarea no existe",
                "code" => 500
            ];
        }
    }

    public function saveFileOpenTask($file, $task_id, $type) {
        $generalFileController = new GeneralFileController();
        $ffile = $file;
        $ffile = str_replace('/tmp/', '', $ffile);
        $filename = sha1(time() . $ffile);
        $extension = $file->getClientOriginalExtension();
        $nameFile = $filename . '_' . $file->getClientOriginalName() . '.' . $extension;

        if ($extension == "png" || $extension == "jpg" || $extension == "jpeg" || $extension == "JPEG" || $extension == "JPG" || $extension == "PNG") {
            $generalFileController->storageImage($nameFile, $file);
        } else {
            Storage::disk('local')->put('documents/' . $nameFile, File::get($file));
        }

        $file_open = new CvFileOpen();
        $file_open->name = $nameFile;
        $file_open->task_open_id = $task_id;
        $file_open->state_delete = 0;
        $file_open->type = $type;
        $file_open->save();
        return $file_open->id;
    }

    public function getFiles($id_task) {

        $task_open = CvTaskOpen::find($id_task);
        if ($task_open) {
            $allFilesTaskOpen = $task_open->openFiles;

            foreach ($allFilesTaskOpen as $file) {
                if ($file->type == 0) {
                    $file['show_name'] = 'Archivos Varios';
                } else if ($file->type == 1) {
                    $file['show_name'] = 'Certificado';
                } else if ($file->type == 2) {
                    $file['show_name'] = 'Factura';
                } else if ($file->type == 3) {
                    $file['show_name'] = 'Pago Parafiscal';
                }
            }

            return $allFilesTaskOpen;
        } else {
            return [];
        }
    }

    public function verifiedFiles($id_task) {

        $task_open = CvTaskOpen::find($id_task);
        if ($task_open) {
            $allFilesTaskOpen = $task_open->openFiles;

            $typeFileOne = false;
            $typeFileTwo = false;
            $typeFileThree = false;

            foreach ($allFilesTaskOpen as $file) {
                if ($file->type == 1) {
                    $typeFileOne = true;
                } else if ($file->type == 2) {
                    $typeFileTwo = true;
                } else if ($file->type == 3) {
                    $typeFileThree = true;
                }

                if ($typeFileOne & $typeFileTwo & $typeFileThree) {
                    return [
                        'verified' => true
                    ];
                }
            }

            return [
                'verified' => false
            ];
        } else {
            return [];
        }
    }

    public function deleteFile($id_file) {
        $file = CvFileOpen::findOrFail($id_file);
        $file->state_delete = 1;
        $file->save();

        return [
            'message' => 'Archivo eliminado',
            'code' => 200
        ];
    }

    public function commandActivitesContribution($type) {
        //Busca contribuciones a la actividad
        if ($type == 'psa') {
            $comand = CvAssociatedContribution::where('project_activity_id', 9)->where('type', 1)->get();
        } else if ($type == 'erosion') {
            $comand = CvAssociatedContribution::where('project_activity_id', 7)->get();
        } else if ($type == 'plan') {
            $comand = CvAssociatedContribution::where('project_activity_id', 2)->get();
        } else if ($type == 'experiencias') {
            $comand = CvAssociatedContribution::where('project_activity_id', 3)->get();
        } else if ($type == 'encuentro') {
            $comand = CvAssociatedContribution::where('project_activity_id', 4)->get();
        } else if ($type == 'hidrico') {
            $comand = CvAssociatedContribution::where('project_activity_id', 14)->get();
        } else if ($type == 'stards') {
            $comand = CvAssociatedContribution::where('project_activity_id', 8)->get();
        } else {
            return[
                "message" => "El tipo " . $type . " no es valido, entre los dados seleccione psa, erosion, hidrico, plan, encuentro o experiencias",
                "code" => 500
            ];
        }

        if ($comand->isEmpty()) {
            return[
                "message" => "No hay contribuciones de tipo " . $type,
                "code" => 500
            ];
        }

        //Funcion para armar el obejot de tareas especiales para un presupuesto
        $info = new FunctionsSpecificController();
        return $info->objectForTaskSpecial($comand);
    }

    public function commandActivitesContributionSpecie($type) {
        //Busca contribuciones a la actividad
        $comand = CvAssociatedContribution::where('project_activity_id', $type)->where('type', 2)->get();
        if ($comand->isEmpty()) {
            return[
                "message" => "No hay contribuciones para la actividad " . $type,
                "code" => 500
            ];
        }

        //Funcion para armar el obejot de tareas especiales para un presupuesto
        $info = new FunctionsSpecificController();
        $contri = $info->objectForTaskSpecial($comand);
        return $contri['type_2'];
    }

    public function communicationForm(Request $dates) {
        if ($dates->form_id != 0) {
            //   $file=CvFilesFormComunication::where('formsjson_id',$dates->form_id)->delete();
            $comunication = CvCommunicationFormsJson::find($dates->form_id);
            // $comunication->delete();
        } else {
            $comunication = new CvCommunicationFormsJson();
        }

        $comunication->formjson = json_encode($dates->all(), true);
        $comunication->user_id = $this->userLoggedInId();
        $comunication->task_id = $dates->task_id;
        $comunication->type = $dates->type;
        $comunication->save();

        $this->registFilesCommunicationForm($comunication, $dates);

        return[
            "message" => "Formulario almacenado",
            "code" => 200
        ];
    }

    public function getCommunicationForm($id_task) {

        $form = CvTaskOpen::find($id_task);
        $info = array();
        foreach ($form->getFormCommunication(1)->get() as $detail) {

            $json = json_decode($detail->formjson, true);
            $json['id'] = $detail->id;
            $json['images'] = $detail->getFileCommunication;

            array_push($info, $json);
        }
        return $info;
    }

    public function updateCommunicationForm(Request $dates) {
        $comunication = CvCommunicationFormsJson::find($dates->form_id);

        $comunication->formjson = json_encode($dates->all(), true);
        $comunication->user_id = $this->userLoggedInId();
        $comunication->task_id = $dates->task_id;
        $comunication->type = $dates->type;
        $comunication->save();

        $this->registFilesCommunicationForm($comunication, $dates);

        return[
            "message" => "Formulario almacenado",
            "code" => 200
        ];
    }

    public function registFilesCommunicationForm($comunication, $dates) {
        $generalFileController = new GeneralFileController();
        $arrayFiles = $dates->file('images');

        if (is_array($arrayFiles) && !empty($arrayFiles)) {

            foreach ($arrayFiles as $file) {

                $typeFile = strtolower(File::extension($file->getClientOriginalName()));

                $nameFile = $generalFileController->codeRandomFiles() . "_" . $file->getClientOriginalName();
                //--- Filtrar imagenes ---//

                if ($typeFile == "png" || $typeFile == "jpg" || $typeFile == "jpeg" || $typeFile == "JPG" || $typeFile == "JPEG" || $typeFile == "PNG") {

                    $generalFileController->storageImage($nameFile, $file);
                }

                if ($typeFile == "pdf" || $typeFile == "xlsx" || $typeFile == "xls" || $typeFile == "doc" || $typeFile == "docx") {
                    Storage::disk('local')->put('documents/' . $nameFile, File::get($file));
                }

                $file_open = new CvFilesFormComunication();
                $file_open->name = $nameFile;
                $file_open->formsjson_id = $comunication->id;
                $file_open->state_delete = 0;
                $file_open->save();
            }
        }
    }

    public function getComment($type, $task_id) {
        $date = Carbon::now();
        $info = array();
        $comment = CvCommentByOtherTask::where('type', $type)->where('task_id', $task_id)->get();
        foreach ($comment as $detail) {
            $detail->comment;

            array_push($info, array(
                "comment_id" => $detail->id,
                "task_id" => $detail->task_id,
                "user_id" => $detail->user_id,
                "user_name" => $detail->user->name,
                "type" => $detail->type,
                "description" => $detail->comment->description,
                "created_at" => $date->format($detail->created_at)
            ));
        }
        return $info;
    }

    public function addComment(Request $dates) {
        $comment = new CvComment();
        $comment->description = $dates->description;
        $comment->save();

        $relation = new CvCommentByOtherTask();
        $relation->task_id = $dates->task_id;
        $relation->type = $dates->type;
        $relation->comment_id = $comment->id;
        $relation->user_id = $this->userLoggedInId();
        $relation->save();

        return [
            "message" => "Comentario registrado",
            "code" => 200
        ];
    }

    public function nextTaskSubtype(Request $dates) {
        $campos = $dates->all();

        $task = CvTaskOpen::find($dates->task_id);

        if ($task->task_open_sub_type_id == 26) {
            $task->user_id = $dates->user_id;
            $task->save();
            return [
                "message" => "La tarea a cambiado su estado",
                "code" => 200
            ];
        }

        if ($task->task_open_sub_type_id == 2 && User::find($task->user_id)->role_id == 3) {
            $task->user_id = $dates->user_id;
            $task->save();
            return [
                "message" => "La tarea a cambiado su estado",
                "code" => 200
            ];
        }


        if ($task->task_open_sub_type_id == $task->subtypes->go_to) {
            return [
                "message" => "La tarea no puedo continuar un flujo",
                "code" => 500
            ];
        }
        if (!array_key_exists('reasign', $campos)) {
            $task->task_open_sub_type_id = $task->subtypes->go_to;
        }

        switch ($task->task_open_sub_type_id) {
            case 19:
                $validate = CvFormatDetallCotractor::where('task_id', $dates->task_id)->exists();
                if (!$validate) {
                    return [
                        "message" => "Debe cargar un formulario de contrato para poder avanzar",
                        "code" => 500
                    ];
                }
                $task->user_id = User::where('role_id', 9)->inRandomOrder()->first()->id;
                break;
            case 5:
                $task->user_id = User::where('role_id', 13)->inRandomOrder()->first()->id;
                break;
            case 24:
                $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;
                break;
            case 25:
                $task->user_id = $task->user_id;
                break;
            case 29:
                $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;
                break;
            case 30:
                $task->user_id = $task->user_id;
                break;
            case 38:
                $task->user_id = CvBackupTaskOpenAndEspecial::where('task_open_id', $dates->task_id)->first()->to_user;
                break;
            case 39:
                $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;
                break;
            case 41:
                $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;
                break;
            case 42:
                $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;
                break;
            default:
                $task->user_id = $dates->user_id;
                break;
        }
        $task->save();

        return [
            "message" => "La tarea a cambiado su estado",
            "code" => 200
        ];
    }

    //Crea tareas abertas especiales en este caso solo tareas para contratistas en caso de crearce mas tareas especiales modificar el servicio
    public function createTaskOpenSpecial(Request $date) {
        $task_execution = CvTaskExecution::find($date->id);
        $process = $task_execution->taskExecutionByUser->actionByUserContractor->poolProcess->Process;

        $task_open = new CvTaskOpen();
        $task_open->description = $date->description;
        $task_open->date_start = $date->date_start;
        $task_open->date_end = $date->date_end;
        $task_open->task_status_id = 1;
        $task_open->process_id = $process->id;
        $task_open->user_id = $date->user_id_contractor;

        switch ($date->type_process) {
            case 'contratista':
                $task_open->task_open_sub_type_id = 18;
                break;
        }

        $task_open->save();

        if ($task_open->task_open_sub_type_id == 18) {
            $newtask_execution = new CvTaskOpenByTaskExecution();
            $newtask_execution->task_execution = $task_execution->id;
            $newtask_execution->task_open = $task_open->id;
            $newtask_execution->save();
        }

        return [
            "message" => "Tarea abierta creada",
            "code" => 200
        ];
    }

    public function insertformTaskOpen(Request $alldata, $task_id) {
        $task = CvTaskOpen::find($task_id);

        if ($task->task_open_sub_type_id != 21 && $task->task_open_sub_type_id != 26 && $task->task_open_sub_type_id != 2) {
            return [
                "message" => "La tarea no se encuentra en el subtipo correspondiente",
                "code" => 500
            ];
        }
        foreach ($alldata->data as $data) {
            foreach ($data['form'] as $form) {
                $formatSowing = new CvCommunicationFormsJson();
                $formatSowing->formjson = json_encode($form, true);
                $formatSowing->user_id = $this->userLoggedInId();
                $formatSowing->type = 1;
                if (array_key_exists('hash', $data)) {
                    $formatSowing->hash = $data['hash'];
                }
                $formatSowing->task_id = $alldata->task_id;
                $formatSowing->save();
            }
        }
        if ($task->task_open_sub_type_id == 21) {
            $task->task_open_sub_type_id = 22;
        }
        if ($task->task_open_sub_type_id == 26) {
            $task->task_open_sub_type_id = 27;
        }
        if ($task->task_open_sub_type_id == 2) {
            $task->task_open_sub_type_id = 39;
        }
        $task->user_id = User::where('role_id', 10)->inRandomOrder()->first()->id;

        $geomap = new CvTaskOpenGeoMap();
        if ($task->task_open_sub_type_id == 22) {
            $geomap->type = 1; //hidrico
        }
        if ($task->task_open_sub_type_id == 27) {
            $geomap->type = 2; //erosivo
        }
        if ($task->task_open_sub_type_id == 39) {
            $geomap->type = 3; //psa
        }
        $geomap->mapjson = json_encode($alldata->geojson, true);
        $geomap->task_open_id = $task_id;

        $task->save();
        $geomap->save();

        return [
            "message" => "Formulario almacenado",
            "code" => 200
        ];
    }

    public function bringCoordinates($task_id) {
        $info = array();
        $task = CvTaskOpen::find($task_id);
        if (empty($task->process->potential_property_id)) {
            return[];
        }
        $main_coordinate = explode(",", $task->process->potentialProperty->main_coordinate);
        $info['lat'] = $main_coordinate[0];
        $info['lng'] = $main_coordinate[1];

        return $info;
    }

    public function getGeoMap($task_id) {
        $info = array();
        $task = CvTaskOpenGeoMap::where('task_open_id', $task_id)->get()->last();
        $info['task_id'] = $task_id;
        $info['geojson'] = json_decode($task->mapjson, true);
        return $info;
    }

    public function insertGeoMap(Request $alldata) {
        $geomap = new CvTaskOpenGeoMap();
        $task = CvTaskOpen::find($alldata->task_id);

        switch ($task->task_open_sub_type_id) {
            case 23:
                $geomap->type = 1;
                break;
            case 28:
                $geomap->type = 2;
                break;
            case 2:
            case 39:
            case 40:
            case 41:
            case 42:
                $geomap->type = 3;
                break;
            default:
                return [
                    "message" => "La tarea no esta en el subtipo correspondiente",
                    "code" => 200
                ];
                break;
        }

        $geomap->mapjson = json_encode($alldata->geojson, true);
        $geomap->task_open_id = $alldata->task_id;

        $geomap->save();

        return [
            "message" => "Mapa editado",
            "code" => 200
        ];
    }

    //Excel de tabla communication_form_jsons sin filtro de fecha
    public function dowloadSamplesTaskOpens($id_task) {
        $info = array();
        $forms = array();
        $has = array();
        $comparison = CvCommunicationFormsJson::where('type', 1)->where('task_id', $id_task)->get();

        foreach ($comparison as $map) {
            $all_map = json_decode($map->formjson, true);
            $all_map['hash'] = $map->hash;
            array_push($forms, $all_map);
        }

        $info['forms'] = $forms;

        $task = CvTaskOpen::find($id_task);
        if (empty($task)) {
            return [
                "message" => "La tarea no existe",
                "code" => 500
            ];
        }

        if ($task->task_open_sub_type_id == 4 || $task->task_open_sub_type_id == 21 || $task->task_open_sub_type_id == 22 || $task->task_open_sub_type_id == 23 || $task->task_open_sub_type_id == 24 || $task->task_open_sub_type_id == 25) {
            $other = CvOtherCampsTaskOpens::where('task_id', $id_task)->where('type', 1);
            if ($other->exists()) {
                $info['other'] = json_decode($other->first()->formjson, true);
            } else {
                $info['other'] = null;
            }
            $maperosivo = CvTaskOpenGeoMap::where('task_open_id', $id_task)->get()->last();
            if (empty($maperosivo)) {
                $info['map_valide'] = 0;
                $info['map'] = [];
            } else {
                $info['map_valide'] = 1;
                $taskgeomap = json_decode($maperosivo->mapjson, true);
                $info['map'] = $taskgeomap;
            }

            $this->ExcelHidrico($info);
        }
        if ($task->task_open_sub_type_id == 3 || $task->task_open_sub_type_id == 26 || $task->task_open_sub_type_id == 27 || $task->task_open_sub_type_id == 28 || $task->task_open_sub_type_id == 29 || $task->task_open_sub_type_id == 30) {
            $maperosivo = CvTaskOpenGeoMap::where('task_open_id', $id_task)->get()->last();
            if (empty($maperosivo)) {
                return [
                    "message" => "no hay mapa relacionado a la tarea",
                    "code" => 500
                ];
            }
            $taskgeomap = json_decode($maperosivo->mapjson, true);
            $info['map'] = $taskgeomap;
            $this->ExcelErosivo($info);
        }

        if ($task->task_open_sub_type_id == 5 || $task->task_open_sub_type_id == 6 || $task->task_open_sub_type_id == 8) {
            $this->ExcelComunication($info);
        }
    }

    //Excel de tabla communication_form_jsons filtrado por fecha
    public function dowloadSamplesTaskOpensFilterData(Request $dates, $id_task) {

        $info = array();
        $forms = array();
        $has = array();
        $from = date(date($dates->from, strtotime("-1 month")) . ' 00:00:00', time()); //need a space after dates.
        $to = date($dates->to . ' 23:59:59', time());

        $comparison = CvCommunicationFormsJson::whereBetween('created_at', array($from, $to))->where('type', 1)->where('task_id', $id_task)->get();


        foreach ($comparison as $map) {
            $all_map = json_decode($map->formjson, true);
            $all_map['hash'] = $map->hash;
            array_push($forms, $all_map);
        }

        $info['forms'] = $forms;

        $task = CvTaskOpen::find($id_task);
        if (empty($task)) {
            return [
                "message" => "La tarea no existe",
                "code" => 500
            ];
        }

        if ($task->task_open_sub_type_id == 4 || $task->task_open_sub_type_id == 21 || $task->task_open_sub_type_id == 22 || $task->task_open_sub_type_id == 23 || $task->task_open_sub_type_id == 24 || $task->task_open_sub_type_id == 25) {
            $other = CvOtherCampsTaskOpens::where('task_id', $id_task)->where('type', 1);
            if ($other->exists()) {
                $info['other'] = json_decode($other->first()->formjson, true);
            } else {
                $info['other'] = null;
            }
            $maperosivo = CvTaskOpenGeoMap::where('task_open_id', $id_task)->get()->last();
            if (empty($maperosivo)) {
                $info['map_valide'] = 0;
                $info['map'] = [];
            } else {
                $info['map_valide'] = 1;
                $taskgeomap = json_decode($maperosivo->mapjson, true);
                $info['map'] = $taskgeomap;
            }

            $this->ExcelHidrico($info);
        }
        if ($task->task_open_sub_type_id == 3 || $task->task_open_sub_type_id == 26 || $task->task_open_sub_type_id == 27 || $task->task_open_sub_type_id == 28 || $task->task_open_sub_type_id == 29 || $task->task_open_sub_type_id == 30) {
            $maperosivo = CvTaskOpenGeoMap::where('task_open_id', $id_task)->get()->last();
            if (empty($maperosivo)) {
                return [
                    "message" => "no hay mapa relacionado a la tarea",
                    "code" => 500
                ];
            }
            $taskgeomap = json_decode($maperosivo->mapjson, true);
            $info['map'] = $taskgeomap;
            $this->ExcelErosivo($info);
        }

        if ($task->task_open_sub_type_id == 5 || $task->task_open_sub_type_id == 6 || $task->task_open_sub_type_id == 8) {
            $this->ExcelComunication($info);
        }
    }

    private function ExcelHidrico($info) {

        Excel::create('Laravel Excel', function($excel) use ($info) {
            $excel->sheet('Productos', function($sheet) use ($info) {


                if ($info['other'] == null) {
                    $other['place'] = "";
                    $other['user'] = "";
                    $other['sampleType'] = "";
                    $other['sampleQuantity'] = "";
                    $other['basin'] = "";
                    $other['dateAndTime'] = "";
                    $other['type'] = "";
                    $other['estimatedTimeFrame'] = "";
                    $other['stream'] = "";
                } else {

                    $other['place'] = $info['other']['place'];
                    $other['user'] = $info['other']['user'];
                    $other['sampleType'] = $info['other']['sampleType'];
                    $other['sampleQuantity'] = $info['other']['sampleQuantity'];
                    $other['basin'] = $info['other']['basin'];
                    $other['dateAndTime'] = $info['other']['dateAndTime'];
                    $other['type'] = $info['other']['type'];
                    $other['estimatedTimeFrame'] = $info['other']['estimatedTimeFrame'];
                    $other['stream'] = $info['other']['stream'];
                }

                $sheet->row(1, [
                    'COMENTARIO', 'NOMBRE', 'COMPONENTE_DE_MONITOREO', 'NUMERO_DE_MONITOREO', 'ESCALA_DE_MONITOREO', 'CLASIFICACION_DE_LOS_PUNTOS', 'ID_MUESTRA', 'LUGAR', 'USUARIO', 'TIPO_EJEMPLO', 'CANTIDAD_MUESTRA', 'CUENCA', 'FECHA_Y_HORA', 'TIPO', 'TIEMPO_ESTIMADO', 'CORRIENTE', 'CONDUCTIVIDAD', 'CARACTERISTICAS', 'HORA', 'OD', 'phUOfPH', 'PUNTO_CLASIFICACION', 'REDOX', 'AMBIENTE_TEMPERATURA', 'satOd', 'sdt', 'TURBIEDAD', 'TEMPERATURA_AGUA', 'TIPO', 'COORDENADA_X', 'COORDENADA_Y'
                ]);
                if (count($info['forms']) == 0) {
                    $sheet->row(2, [
                        "", "", "", "", "", "", "", $other['place'], $other['user'], $other['sampleType'], $other['sampleQuantity'], $other['basin'], $other['dateAndTime'], $other['type'], $other['estimatedTimeFrame'], $other['stream'], "", "", "", "", "", "", "", "", "", "", "", "", "", 0, 0
                    ]);
                }
                foreach ($info['forms'] as $index => $user) {
                    $X = 0;
                    $Y = 0;

                    if ($info['map_valide'] == 1) {
                        foreach ($info['map']['features'] as $feature) {
                            if (!empty($feature['properties']['hash'])) {
                                if ($feature['properties']['hash'] == $user['hash']) {
                                    $X = ($feature['geometry']['coordinates'][0]);
                                    $Y = ($feature['geometry']['coordinates'][1]);
                                }
                            }
                        }
                    }
                    $sheet->row($index + 2, [
                        $user['comments'], $user['commonName'], $user['monitoringComponent'], $user['monitoringNumber'], $user['monitoringScale'], $user['pointClassification'], $user['sampleId'], $other['place'], $other['user'], $other['sampleType'], $other['sampleQuantity'], $other['basin'], $other['dateAndTime'], $other['type'], $other['estimatedTimeFrame'], $other['stream'], $user['conductivity'], $user['feature'], $user['hour'], $user['od'], $user['phUOfPH'], $user['pointClassification'], $user['redoxPotential'], $user['roomTemperature'], $user['satOd'], $user['sdt'], $user['turbidity'], $user['waterTemperature'], $user['type'], $X, $Y
                    ]);
                }
            });
        })->export('xls');
    }

    private function ExcelErosivo($info) {
        Excel::create('Laravel Excel', function($excel) use ($info) {
            $excel->sheet('Productos', function($sheet) use ($info) {


                $sheet->row(1, [
                    'FECHA_DE_IDENTIFICACION', 'CUENCA', 'MUNICIPIO', 'VEREDA', 'FUENTE_HIDRICA', 'ESTADO', 'COORDENADA_X', 'COORDENADA_Y'
                ]);
                foreach ($info['forms'] as $index => $user) {
                    if ($user['laFe']) {
                        $basin = "La Fe";
                    } else {
                        $basin = "Rio Grande";
                    }
                    $X = 0;
                    $Y = 0;
                    foreach ($info['map']['features'] as $feature) {
                        if (!empty($feature['properties']['hash'])) {
                            if ($feature['properties']['hash'] == $user['hash']) {
                                $X = ($feature['geometry']['coordinates'][0]);
                                $Y = ($feature['geometry']['coordinates'][1]);
                            }
                        }
                    }

                    $sheet->row($index + 2, [
                        $user['identificationDate']['year'], $basin, $user['municipality'], $user['lane'], $user['hydrologicalSource'], $user['status'], $X, $Y
                    ]);
                }
            });
        })->export('xls');
    }

    private function ExcelComunication($info) {
        Excel::create('Laravel Excel', function($excel) use ($info) {
            $excel->sheet('Productos', function($sheet) use ($info) {

                $sheet->fromArray($info['forms']);
            });
        })->export('xls');
    }

    //Trae el ultimo exel de tareas abiertas
    public function getInfOpenExel($task_id, $type) {
        $info = array();
        $excel = CvFilePsa::where('task_open_id', $task_id)->where('type', $type)->get()->last();
        $user = $excel->user;
        $info['user'] = $user->name;
        $info['create_at'] = $user->created_at->format('Y-m-d H:i:s');

        return $info;
    }

    //Almacena archivos por formulario cargado en tareas abiertas
    public function insertDocumentForms(Request $data) {
        $validate = 0;
        $all_forms = CvCommunicationFormsJson::all();
        foreach ($all_forms as $form) {
            $detail_form = json_decode($form->formjson, true);
            if (key_exists('hash', $detail_form)) {
                if ($detail_form['hash'] == $data->hash) {
                    $validate = 1;
                    break;
                }
            }
        }
        if ($validate == 1) {
            $arrayFiles = $data->file('files');

            if (is_array($arrayFiles) && !empty($arrayFiles)) {

                foreach ($arrayFiles as $file) {
                    $this->saveFileForms($file, $data->hash, $data->type);
                }
            }
            return[
                "message" => "Archivo almacenado",
                "code" => 200
            ];
        }
        return[
            "message" => "El formulario no existe",
            "code" => 500
        ];
    }

    public function saveFileForms($file, $hash, $type) {
        $generalFileController = new GeneralFileController();
        $ffile = $file;
        $ffile = str_replace('/tmp/', '', $ffile);
        $filename = sha1(time() . $ffile);
        $extension = $file->getClientOriginalExtension();
        $nameFile = $filename . '_' . $file->getClientOriginalName() . '.' . $extension;

        if ($extension == "png" || $extension == "jpg" || $extension == "jpeg") {
            $generalFileController->diskstorageImage($nameFile, $file);
            $disck = "images/";
        } else {
            Storage::disk('storage')->put('formsopen/' . $nameFile, File::get($file));
            $disck = "formsopen/";
        }

        $file_open = new CvFileForFormsTaskOpen();
        $file_open->name = $disck . $nameFile;
        $file_open->hash = $hash;
        $file_open->state_delete = 0;
        $file_open->type = $type;
        $file_open->save();
        return $file_open->id;
    }

    public function getDocumentForms($id_task) {
        $images_array = array();

        $info = array();
        $forms = CvCommunicationFormsJson::where('task_id', $id_task)->get();
        foreach ($forms as $detall_form) {
            $json_form = json_decode($detall_form->formjson, true);
            if (key_exists('hash', $json_form)) {
                $all_files = CvFileForFormsTaskOpen::where('hash', $json_form['hash']);
                if ($all_files->exists()) {


                    foreach ($all_files->get() as $detail_file) {
                        $image = Storage::disk('storage')->get($detail_file->name);
                        $forms['image'] = base64_encode($image);

                        array_push($images_array, $forms['image']
                        );
                    }
                    $dataall['images'] = $images_array;
                    $dataall['form_hash'] = $json_form['hash'];


                    array_push($info, $dataall);
                } else {
                    $info = $this->getDataFiles($id_task);
                }
            } else {
                $info = $this->getDataFiles($id_task);
            }
        }
        return $info;
    }

    private function getDataFiles($id_task) {
        $data = Array();

        $modelFiles = CvFileOpen::where('task_open_id', $id_task)->get();
        $getData = new GeneralFileController();

        foreach ($modelFiles as $modelFile) {
            array_push($data, $getData->getallFiles64('images', $modelFile->name));
        }

        return $data;
    }

    public function otherCamps(Request $data) {
        if (CvTaskOpen::where('id', $data->task_id)->exists()) {
            $other_camp = CvOtherCampsTaskOpens::where('task_id', $data->task_id);
            if ($other_camp->exists()) {
                $addother = $other_camp->first();
            } else {

                $addother = new CvOtherCampsTaskOpens();
            }
            $addother->type = 1;
            $addother->task_id = $data->task_id;
            $addother->formjson = json_encode($data->data, true);
            $addother->save();

            return[
                "message" => "Registro almacenado",
                "code" => 200
            ];
        }
        return[
            "message" => "La tarea no existe",
            "code" => 500
        ];
    }

}
